import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../services/api_service.dart';

class AdminScreen extends StatelessWidget {
  const AdminScreen({required this.apiService, required this.user, required this.onLogout, required this.isDarkMode, required this.onThemeChanged, super.key});
  final ApiService apiService;
  final Map<String, dynamic> user;
  final VoidCallback onLogout;
  final bool isDarkMode;
  final ValueChanged<bool> onThemeChanged;

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 6,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('LearnFlow Admin'),
          actions: [IconButton(onPressed: () async { await apiService.logout(); onLogout(); }, icon: const Icon(Icons.logout), tooltip: 'Sign out')],
          bottom: const TabBar(isScrollable: true, tabs: [
            Tab(icon: Icon(Icons.dashboard_outlined), text: 'Dashboard'),
            Tab(icon: Icon(Icons.people_outline), text: 'Users'),
            Tab(icon: Icon(Icons.menu_book_outlined), text: 'Courses'),
            Tab(icon: Icon(Icons.payments_outlined), text: 'Payments'),
            Tab(icon: Icon(Icons.analytics_outlined), text: 'Reports'),
            Tab(icon: Icon(Icons.history), text: 'Audit history'),
          ]),
        ),
        body: TabBarView(children: [
          _AdminOverview(apiService: apiService),
          _UsersTab(apiService: apiService),
          _CoursesTab(apiService: apiService),
          _PaymentsTab(apiService: apiService),
          _ReportsTab(apiService: apiService),
          _AuditTab(apiService: apiService),
        ]),
      ),
    );
  }
}

class _AdminOverview extends StatelessWidget {
  const _AdminOverview({required this.apiService});
  final ApiService apiService;
  @override
  Widget build(BuildContext context) => FutureBuilder<Map<String, dynamic>>(
    future: apiService.fetchDashboard('admin'),
    builder: (context, snapshot) {
      if (snapshot.hasError) return const _AdminState(icon: Icons.cloud_off, text: 'Dashboard unavailable. Pull down to retry.');
      if (!snapshot.hasData) return const Center(child: CircularProgressIndicator());
      final stats = _maps(snapshot.data!['stats']);
      return RefreshIndicator(onRefresh: () async { await apiService.fetchDashboard('admin'); }, child: ListView(padding: const EdgeInsets.all(16), children: [
        Text('Platform overview', style: Theme.of(context).textTheme.headlineSmall),
        const SizedBox(height: 4), const Text('Monitor the learning operation from one place.'),
        const SizedBox(height: 20),
        LayoutBuilder(builder: (context, constraints) { final columns = constraints.maxWidth > 650 ? 4 : 2; return GridView.builder(shrinkWrap: true, physics: const NeverScrollableScrollPhysics(), itemCount: stats.length, gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: columns, mainAxisSpacing: 12, crossAxisSpacing: 12, childAspectRatio: 1.5), itemBuilder: (_, index) { final stat = stats[index]; return Semantics(label: '${stat['label']}: ${stat['value']}', child: Card(child: Padding(padding: const EdgeInsets.all(14), child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Text('${stat['label'] ?? ''}'), Text('${stat['value'] ?? ''}', style: Theme.of(context).textTheme.headlineMedium), Text('${stat['trend'] ?? ''}', style: TextStyle(color: Theme.of(context).colorScheme.primary))])))); }); }),
        const SizedBox(height: 24), Card(child: ListTile(leading: const Icon(Icons.shield_outlined), title: const Text('Admin controls protected'), subtitle: const Text('All management data is loaded through admin-authorized API actions.'))),
      ]));
    },
  );
}

class _UsersTab extends StatefulWidget {
  const _UsersTab({required this.apiService});

  final ApiService apiService;

  @override
  State<_UsersTab> createState() => _UsersTabState();
}

class _UsersTabState extends State<_UsersTab> {
  List<Map<String, dynamic>> _items = <Map<String, dynamic>>[];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final users = await widget.apiService.fetchAdminUsers();
      if (!mounted) return;
      setState(() {
        _items = users;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Could not load users.')),
      );
    }
  }

  Future<void> _delete(Map<String, dynamic> item) async {
    final userId = int.tryParse('${item['id'] ?? ''}');
    if (userId == null) return;

    final confirmed = await showDialog<bool>(
          context: context,
          builder: (dialogContext) => AlertDialog(
            title: const Text('Delete user?'),
            content: Text(
              'Remove ${item['email'] ?? 'this user'} from the platform?',
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(dialogContext, false),
                child: const Text('Cancel'),
              ),
              FilledButton(
                onPressed: () => Navigator.pop(dialogContext, true),
                child: const Text('Delete'),
              ),
            ],
          ),
        ) ??
        false;

      if (!mounted || !confirmed) return;

    try {
      await widget.apiService.deleteUser(userId);
      if (!mounted) return;
      setState(() => _items.removeWhere((user) => '${user['id']}' == '$userId'));
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('User deleted.')),
      );
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Could not delete user.')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }

    return RefreshIndicator(
      onRefresh: _load,
      child: _items.isEmpty
          ? ListView(
              children: const [
                SizedBox(height: 180),
                _AdminState(icon: Icons.people_outline, text: 'No users found.'),
              ],
            )
          : ListView(
              padding: const EdgeInsets.all(16),
              children: _items.map((item) {
                final firstName = '${item['first_name'] ?? 'U'}';
                final initial = firstName.isEmpty ? 'U' : firstName[0].toUpperCase();
                return Card(
                  child: ListTile(
                    leading: CircleAvatar(child: Text(initial)),
                    title: Text(
                      '${item['first_name'] ?? ''} ${item['last_name'] ?? ''}'.trim(),
                    ),
                    subtitle: Text(
                      '${item['email'] ?? ''}\n${item['role'] ?? 'user'}',
                    ),
                    isThreeLine: true,
                    trailing: IconButton(
                      onPressed: () => _delete(item),
                      icon: const Icon(Icons.delete_outline),
                      tooltip: 'Delete user',
                    ),
                  ),
                );
              }).toList(),
            ),
    );
  }
}

class _CoursesTab extends StatelessWidget { const _CoursesTab({required this.apiService}); final ApiService apiService; @override Widget build(BuildContext context) => _AdminRemoteList(future: apiService.fetchAdminCourses(), emptyIcon: Icons.menu_book_outlined, emptyText: 'No courses found.', builder: (items) => ListView(padding: const EdgeInsets.all(16), children: items.map((item) => Card(child: ListTile(leading: const Icon(Icons.school_outlined), title: Text('${item['title'] ?? 'Untitled course'}'), subtitle: Text('${item['category'] ?? ''}  |  ${item['level'] ?? ''}\nInstructor: ${item['instructor'] ?? 'Unassigned'}'), isThreeLine: true, trailing: Text('${item['status'] ?? 'Active'}')))).toList())); } }
class _PaymentsTab extends StatelessWidget { const _PaymentsTab({required this.apiService}); final ApiService apiService; @override Widget build(BuildContext context) => _AdminRemoteList(future: apiService.fetchAdminPayments(), emptyIcon: Icons.payments_outlined, emptyText: 'No payment records found.', builder: (items) => ListView(padding: const EdgeInsets.all(16), children: items.map((item) => Card(child: ListTile(leading: const Icon(Icons.receipt_long_outlined), title: Text('${item['course_title'] ?? 'Course payment'}'), subtitle: Text('${item['student_email'] ?? ''}\n${item['payment_method'] ?? 'Payment'}'), isThreeLine: true, trailing: Column(mainAxisAlignment: MainAxisAlignment.center, crossAxisAlignment: CrossAxisAlignment.end, children: [Text('${item['amount'] ?? 0} ${item['currency'] ?? 'ETB'}', style: const TextStyle(fontWeight: FontWeight.bold)), Text('${item['status'] ?? 'Pending'}')])))).toList())); } }
class _ReportsTab extends StatelessWidget {
  const _ReportsTab({required this.apiService});
  final ApiService apiService;

  @override
  Widget build(BuildContext context) => FutureBuilder<Map<String, dynamic>>(
        future: apiService.fetchAdminReport(),
        builder: (context, snapshot) {
          if (snapshot.hasError) return const _AdminState(icon: Icons.cloud_off, text: 'Reports unavailable. Try again later.');
          if (!snapshot.hasData) return const Center(child: CircularProgressIndicator());
          final report = snapshot.data!;
          return ListView(padding: const EdgeInsets.all(16), children: [
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Text('Reports', style: Theme.of(context).textTheme.headlineSmall), IconButton(onPressed: () => _export(context, report), icon: const Icon(Icons.download_outlined), tooltip: 'Export CSV')]),
            const SizedBox(height: 8),
            _section(context, 'Learning report', Icons.school_outlined, report['learning'] as Map<String, dynamic>? ?? {}),
            _section(context, 'Instructor report', Icons.co_present_outlined, report['instructor'] as Map<String, dynamic>? ?? {}),
            _section(context, 'Financial report', Icons.payments_outlined, report['financial'] as Map<String, dynamic>? ?? {}),
            const SizedBox(height: 8), OutlinedButton.icon(onPressed: null, icon: const Icon(Icons.picture_as_pdf_outlined), label: const Text('Export PDF - coming soon')),
          ]);
        },
      );

  Widget _section(BuildContext context, String title, IconData icon, Map<String, dynamic> values) => Card(margin: const EdgeInsets.only(bottom: 14), child: Padding(padding: const EdgeInsets.all(16), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Row(children: [Icon(icon, color: Theme.of(context).colorScheme.primary), const SizedBox(width: 8), Text(title, style: Theme.of(context).textTheme.titleLarge)]), const Divider(), ...values.entries.map((entry) => ListTile(contentPadding: EdgeInsets.zero, title: Text(_label(entry.key)), trailing: Text('${entry.value}', textAlign: TextAlign.end, style: const TextStyle(fontWeight: FontWeight.bold))))])));
  String _label(String key) => key.replaceAll('_', ' ').split(' ').map((word) => word.isEmpty ? word : '${word[0].toUpperCase()}${word.substring(1)}').join(' ');
  void _export(BuildContext context, Map<String, dynamic> report) { final rows = <List<String>>[['Report', 'Metric', 'Value']]; for (final group in report.entries) { final values = group.value as Map<String, dynamic>; for (final entry in values.entries) rows.add([group.key, entry.key, '${entry.value}']); } final csv = rows.map((row) => row.map((value) => '"${value.replaceAll('"', '""')}"').join(',')).join('\n'); showDialog<void>(context: context, builder: (dialogContext) => AlertDialog(title: const Text('CSV export'), content: SingleChildScrollView(child: SelectableText(csv)), actions: [TextButton.icon(onPressed: () { Clipboard.setData(ClipboardData(text: csv)); Navigator.pop(dialogContext); ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('CSV copied to clipboard.'))); }, icon: const Icon(Icons.copy), label: const Text('Copy CSV')), TextButton(onPressed: () => Navigator.pop(dialogContext), child: const Text('Close'))])); }
}
class _AuditTab extends StatelessWidget { const _AuditTab({required this.apiService}); final ApiService apiService; @override Widget build(BuildContext context) => _AdminRemoteList(future: apiService.fetchAuditHistory(), emptyIcon: Icons.history, emptyText: 'No audit events found.', builder: (items) => ListView(padding: const EdgeInsets.all(16), children: items.map((item) => Card(child: ListTile(leading: const Icon(Icons.security_outlined), title: Text('${item['action'] ?? 'Event'}'), subtitle: Text('${item['details'] ?? 'No details'}\n${item['created_at'] ?? ''}  |  ${item['ip_address'] ?? ''}'), isThreeLine: true, trailing: Text('${item['status'] ?? 'success'}')))).toList())); } }

class _AdminRemoteList extends StatelessWidget { const _AdminRemoteList({required this.future, required this.builder, required this.emptyIcon, required this.emptyText}); final Future<List<Map<String, dynamic>>> future; final Widget Function(List<Map<String, dynamic>>) builder; final IconData emptyIcon; final String emptyText; @override Widget build(BuildContext context) => FutureBuilder<List<Map<String, dynamic>>>(future: future, builder: (context, snapshot) { if (snapshot.hasError) return const _AdminState(icon: Icons.cloud_off, text: 'Could not load this section. Reopen the tab to retry.'); if (!snapshot.hasData) return const Center(child: CircularProgressIndicator()); if (snapshot.data!.isEmpty) return _AdminState(icon: emptyIcon, text: emptyText); return builder(snapshot.data!); }); }
class _AdminState extends StatelessWidget { const _AdminState({required this.icon, required this.text}); final IconData icon; final String text; @override Widget build(BuildContext context) => Center(child: Padding(padding: const EdgeInsets.all(32), child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [Icon(icon, size: 50, color: Theme.of(context).colorScheme.primary), const SizedBox(height: 12), Text(text, textAlign: TextAlign.center)]))); }
List<Map<String, dynamic>> _maps(dynamic value) => value is List ? value.map((item) => Map<String, dynamic>.from(item as Map)).toList() : <Map<String, dynamic>>[];
