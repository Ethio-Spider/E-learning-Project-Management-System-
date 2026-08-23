import 'package:flutter/material.dart';

import '../services/api_service.dart';

class StudentExperienceScreen extends StatelessWidget {
  const StudentExperienceScreen(
      {required this.apiService, required this.dashboard, super.key});
  final ApiService apiService;
  final Map<String, dynamic> dashboard;

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 5,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Student experience'),
          bottom: const TabBar(isScrollable: true, tabs: [
            Tab(
                icon: Icon(Icons.notifications_outlined),
                text: 'Notifications'),
            Tab(icon: Icon(Icons.calendar_month_outlined), text: 'Calendar'),
            Tab(
                icon: Icon(Icons.workspace_premium_outlined),
                text: 'Certificates'),
            Tab(icon: Icon(Icons.emoji_events_outlined), text: 'Achievements'),
            Tab(icon: Icon(Icons.forum_outlined), text: 'Discussions'),
          ]),
        ),
        body: TabBarView(children: [
          _NotificationsTab(apiService: apiService),
          _CalendarTab(apiService: apiService),
          _CertificatesTab(apiService: apiService),
          _AchievementsTab(items: _maps(dashboard['badges'])),
          _DiscussionsTab(apiService: apiService),
        ]),
      ),
    );
  }

  static List<Map<String, dynamic>> _maps(dynamic value) => value is List
      ? value.map((item) => Map<String, dynamic>.from(item as Map)).toList()
      : <Map<String, dynamic>>[];
}

class _NotificationsTab extends StatelessWidget {
  const _NotificationsTab({required this.apiService});
  final ApiService apiService;

  @override
  Widget build(BuildContext context) => _RemoteList<Map<String, dynamic>>(
        future: apiService.fetchNotifications(),
        emptyIcon: Icons.notifications_none,
        emptyText: 'You are all caught up.',
        builder: (items) => ListView(
            padding: const EdgeInsets.all(16),
            children: items
                .map((item) => Card(
                      child: ListTile(
                        leading: const CircleAvatar(
                            child: Icon(Icons.notifications_outlined)),
                        title: Text(
                            '${item['text'] ?? item['message'] ?? 'New learning update'}'),
                        subtitle: Text('${item['time'] ?? 'Recently'}'),
                      ),
                    ))
                .toList()),
      );
}

class _CalendarTab extends StatelessWidget {
  const _CalendarTab({required this.apiService});
  final ApiService apiService;

  @override
  Widget build(BuildContext context) => _RemoteList<Map<String, dynamic>>(
        future: apiService.fetchSchedule(),
        emptyIcon: Icons.event_available_outlined,
        emptyText: 'Your learning calendar is clear.',
        builder: (items) =>
            ListView(padding: const EdgeInsets.all(16), children: [
          Text('This week', style: Theme.of(context).textTheme.titleLarge),
          const SizedBox(height: 8),
          ...items.map((item) => Card(
                  child: ListTile(
                leading: CircleAvatar(
                    child: Text('${item['day'] ?? '?'}'.substring(0, 1))),
                title: Text('${item['title'] ?? 'Learning session'}'),
                subtitle: Text(
                    '${item['time'] ?? ''}  |  ${item['type'] ?? 'Study'}'),
                trailing: const Icon(Icons.event_outlined),
              ))),
        ]),
      );
}

class _CertificatesTab extends StatefulWidget {
  const _CertificatesTab({required this.apiService});
  final ApiService apiService;
  @override
  State<_CertificatesTab> createState() => _CertificatesTabState();
}

class _CertificatesTabState extends State<_CertificatesTab> {
  Future<void> _verify() async {
    final controller = TextEditingController();
    final code = await showDialog<String>(
        context: context,
        builder: (context) => AlertDialog(
              title: const Text('Verify a certificate'),
              content: TextField(
                  controller: controller,
                  autofocus: true,
                  decoration: const InputDecoration(
                      labelText: 'Certificate ID or code',
                      border: OutlineInputBorder())),
              actions: [
                TextButton(
                    onPressed: () => Navigator.pop(context),
                    child: const Text('Cancel')),
                FilledButton(
                    onPressed: () =>
                        Navigator.pop(context, controller.text.trim()),
                    child: const Text('Verify'))
              ],
            ));
    controller.dispose();
    if (code == null || code.isEmpty || !mounted) return;
    try {
      final result = await widget.apiService.verifyCertificate(code);
      if (!mounted) return;
      showDialog<void>(
          context: context,
          builder: (context) => AlertDialog(
                title: const Row(children: [
                  Icon(Icons.verified, color: Colors.green),
                  SizedBox(width: 8),
                  Text('Certificate verified')
                ]),
                content: Text(
                    '${result['name'] ?? result['title'] ?? 'Certificate'}\nStatus: ${result['status'] ?? 'Valid'}'),
                actions: [
                  TextButton(
                      onPressed: () => Navigator.pop(context),
                      child: const Text('Done'))
                ],
              ));
    } catch (error) {
      if (mounted)
        ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Verification failed: $error')));
    }
  }

  @override
  Widget build(BuildContext context) => Column(children: [
        Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 4),
            child: Align(
                alignment: Alignment.centerRight,
                child: OutlinedButton.icon(
                    onPressed: _verify,
                    icon: const Icon(Icons.verified_outlined),
                    label: const Text('Verify certificate')))),
        Expanded(
            child: _RemoteList<Map<String, dynamic>>(
          future: widget.apiService.fetchCertificates(),
          emptyIcon: Icons.workspace_premium_outlined,
          emptyText: 'Complete a course to earn your first certificate.',
          builder: (items) => ListView(
              padding: const EdgeInsets.all(16),
              children: items
                  .map((item) => Card(
                          child: ListTile(
                        leading: const Icon(Icons.workspace_premium,
                            color: Colors.amber),
                        title: Text(
                            '${item['name'] ?? item['title'] ?? 'Certificate'}'),
                        subtitle: Text(
                            '${item['status'] ?? 'Issued'}  |  ${item['verification_code'] ?? 'Ready to verify'}'),
                        trailing: IconButton(
                            onPressed: _verify,
                            icon: const Icon(Icons.verified_outlined),
                            tooltip: 'Verify certificate'),
                      )))
                  .toList()),
        )),
      ]);
}

class _AchievementsTab extends StatelessWidget {
  const _AchievementsTab({required this.items});
  final List<Map<String, dynamic>> items;
  @override
  Widget build(BuildContext context) {
    if (items.isEmpty)
      return const _StudentEmpty(
          icon: Icons.emoji_events_outlined,
          text: 'Your achievements will appear as you make progress.');
    return GridView.builder(
        padding: const EdgeInsets.all(16),
        gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
            maxCrossAxisExtent: 240,
            mainAxisSpacing: 12,
            crossAxisSpacing: 12,
            childAspectRatio: 1.15),
        itemCount: items.length,
        itemBuilder: (context, index) {
          final item = items[index];
          return Semantics(
              label: 'Achievement ${item['name'] ?? 'unlocked'}',
              child: Card(
                  child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            CircleAvatar(
                                radius: 28,
                                child: Text('${item['icon'] ?? '★'}')),
                            const SizedBox(height: 12),
                            Text('${item['name'] ?? 'Achievement'}',
                                textAlign: TextAlign.center,
                                style: const TextStyle(
                                    fontWeight: FontWeight.bold)),
                            const SizedBox(height: 4),
                            const Text('Unlocked',
                                style: TextStyle(color: Colors.green))
                          ]))));
        });
  }
}

class _DiscussionsTab extends StatelessWidget {
  const _DiscussionsTab({required this.apiService});
  final ApiService apiService;
  @override
  Widget build(BuildContext context) => _RemoteList<Map<String, dynamic>>(
        future: apiService.fetchDiscussions(),
        emptyIcon: Icons.forum_outlined,
        emptyText: 'No discussions have started yet.',
        builder: (items) => ListView(
            padding: const EdgeInsets.all(16),
            children: items
                .map((item) => Card(
                        child: ListTile(
                      leading:
                          const CircleAvatar(child: Icon(Icons.forum_outlined)),
                      title: Text('${item['topic'] ?? 'Discussion'}'),
                      subtitle: Text(
                          '${item['author'] ?? 'Community'}  |  ${item['replies'] ?? 0} replies'),
                      trailing: const Icon(Icons.chevron_right),
                    )))
                .toList()),
      );
}

class _RemoteList<T> extends StatelessWidget {
  const _RemoteList(
      {required this.future,
      required this.builder,
      required this.emptyIcon,
      required this.emptyText});
  final Future<List<T>> future;
  final Widget Function(List<T>) builder;
  final IconData emptyIcon;
  final String emptyText;
  @override
  Widget build(BuildContext context) => FutureBuilder<List<T>>(
      future: future,
      builder: (context, snapshot) {
        if (snapshot.hasError)
          return _StudentEmpty(
              icon: Icons.cloud_off,
              text: 'Could not load this section. Pull to retry.');
        if (!snapshot.hasData)
          return const Center(child: CircularProgressIndicator());
        if (snapshot.data!.isEmpty)
          return _StudentEmpty(icon: emptyIcon, text: emptyText);
        return builder(snapshot.data!);
      });
}

class _StudentEmpty extends StatelessWidget {
  const _StudentEmpty({required this.icon, required this.text});
  final IconData icon;
  final String text;
  @override
  Widget build(BuildContext context) => Center(
      child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
            Icon(icon, size: 50, color: Theme.of(context).colorScheme.primary),
            const SizedBox(height: 12),
            Text(text, textAlign: TextAlign.center)
          ])));
}
