import 'package:flutter/material.dart';

import '../services/api_service.dart';
import 'student_experience_screen.dart';
import 'learning_center_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({required this.apiService, required this.user, required this.onLogout, required this.isDarkMode, required this.onThemeChanged, super.key});
  final ApiService apiService;
  final Map<String, dynamic> user;
  final VoidCallback onLogout;
  final bool isDarkMode;
  final ValueChanged<bool> onThemeChanged;
  @override State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  int _tab = 0;
  Map<String, dynamic> _dashboard = {};
  bool _loading = true;
  @override void initState() { super.initState(); _loadDashboard(); }
  Future<void> _loadDashboard() async {
    setState(() => _loading = true);
    try {
      final data = await widget.apiService.fetchDashboard(widget.user['role']?.toString() ?? 'student');
      if (mounted) setState(() => _dashboard = data);
    } catch (error) { if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error.toString()))); }
    finally { if (mounted) setState(() => _loading = false); }
  }
  @override Widget build(BuildContext context) {
    final pages = [_home(), CoursesScreen(apiService: widget.apiService), AssignmentsScreen(apiService: widget.apiService, initialItems: _maps(_dashboard['assignments'])), NotificationsScreen(apiService: widget.apiService), ProfileScreen(apiService: widget.apiService, user: widget.user, onLogout: widget.onLogout, isDarkMode: widget.isDarkMode, onThemeChanged: widget.onThemeChanged)];
    return Scaffold(
      appBar: AppBar(title: const Text('LearnFlow Pro'), actions: [IconButton(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => LearningCenterScreen(apiService: widget.apiService))), icon: const Icon(Icons.play_lesson_outlined), tooltip: 'Learning center'), IconButton(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => StudentExperienceScreen(apiService: widget.apiService, dashboard: _dashboard))), icon: const Icon(Icons.explore_outlined), tooltip: 'Student experience'), IconButton(onPressed: () => showSearch(context: context, delegate: GlobalSearchDelegate(widget.apiService)), icon: const Icon(Icons.search), tooltip: 'Search all content'), IconButton(onPressed: _loadDashboard, icon: const Icon(Icons.refresh), tooltip: 'Refresh')]),
      body: _loading && _tab == 0 ? const Center(child: CircularProgressIndicator()) : pages[_tab],
      bottomNavigationBar: NavigationBar(selectedIndex: _tab, onDestinationSelected: (index) => setState(() => _tab = index), destinations: const [
        NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home), label: 'Home'),
        NavigationDestination(icon: Icon(Icons.menu_book_outlined), selectedIcon: Icon(Icons.menu_book), label: 'Courses'),
        NavigationDestination(icon: Icon(Icons.assignment_outlined), selectedIcon: Icon(Icons.assignment), label: 'Tasks'),
        NavigationDestination(icon: Icon(Icons.notifications_none), selectedIcon: Icon(Icons.notifications), label: 'Alerts'),
        NavigationDestination(icon: Icon(Icons.person_outline), selectedIcon: Icon(Icons.person), label: 'Profile'),
      ]),
    );
  }
  Widget _home() {
    final stats = _maps(_dashboard['stats']);
    final courses = _maps(_dashboard['courses']);
    final quizzes = _maps(_dashboard['quizzes']);
    return RefreshIndicator(onRefresh: _loadDashboard, child: ListView(padding: const EdgeInsets.all(16), children: [
      Text('Good to see you, ${widget.user['first_name'] ?? widget.user['name'] ?? 'learner'}', style: Theme.of(context).textTheme.headlineSmall),
      const SizedBox(height: 4), const Text('Your next milestone is waiting.'), const SizedBox(height: 20),
      if (stats.isNotEmpty) LayoutBuilder(builder: (context, constraints) { final columns = constraints.maxWidth >= 700 ? 4 : 2; return GridView.builder(shrinkWrap: true, physics: const NeverScrollableScrollPhysics(), gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: columns, mainAxisSpacing: 10, crossAxisSpacing: 10, childAspectRatio: columns == 4 ? 1.8 : 1.6), itemCount: stats.length, itemBuilder: (_, index) { final stat = stats[index]; return Semantics(label: '${stat['label']}: ${stat['value']}', child: Card(child: Padding(padding: const EdgeInsets.all(14), child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Text('${stat['label'] ?? ''}'), Text('${stat['value'] ?? ''}', style: Theme.of(context).textTheme.headlineSmall), Text('${stat['trend'] ?? ''}', style: TextStyle(color: Theme.of(context).colorScheme.primary))])))); }); }),
      const SizedBox(height: 20), Text('Continue learning', style: Theme.of(context).textTheme.titleLarge), const SizedBox(height: 8),
      if (courses.isEmpty) const _EmptyState(icon: Icons.school_outlined, text: 'Browse courses to start your learning path.') else ...courses.take(3).map((course) => Card(child: ListTile(leading: const CircleAvatar(child: Icon(Icons.play_arrow)), title: Text('${course['title'] ?? 'Course'}'), subtitle: Text('${course['category'] ?? ''}  ${course['level'] ?? ''}'), trailing: Text('${course['progress'] ?? 0}%'), onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => CourseDetailScreen(apiService: widget.apiService, course: course))))),
      const SizedBox(height: 12), Text('Quiz checkpoints', style: Theme.of(context).textTheme.titleLarge), const SizedBox(height: 8),
      ...quizzes.map((quiz) => Card(child: ListTile(leading: const Icon(Icons.quiz_outlined), title: Text('${quiz['title'] ?? 'Quiz'}'), subtitle: Text('${quiz['status'] ?? 'Ready'}  |  Score: ${quiz['score'] ?? '-'}'), trailing: const Icon(Icons.chevron_right), onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => QuizScreen(title: '${quiz['title'] ?? 'Quiz'}'))))),
    ]));
  }
  List<Map<String, dynamic>> _maps(dynamic value) => value is List ? value.map((item) => Map<String, dynamic>.from(item as Map)).toList() : [];
}

class CoursesScreen extends StatefulWidget {
  const CoursesScreen({required this.apiService, super.key});
  final ApiService apiService;
  @override State<CoursesScreen> createState() => _CoursesScreenState();
}
class _CoursesScreenState extends State<CoursesScreen> {
  final _search = TextEditingController(); List<Map<String, dynamic>> _courses = []; bool _loading = true;
  @override void initState() { super.initState(); _load(); }
  Future<void> _load() async { setState(() => _loading = true); try { final data = await widget.apiService.fetchCourses(search: _search.text); if (mounted) setState(() => _courses = data); } catch (error) { if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error.toString()))); } finally { if (mounted) setState(() => _loading = false); } }
  @override Widget build(BuildContext context) => Column(children: [Padding(padding: const EdgeInsets.fromLTRB(16, 16, 16, 8), child: TextField(controller: _search, onSubmitted: (_) => _load(), decoration: InputDecoration(hintText: 'Search courses', prefixIcon: const Icon(Icons.search), suffixIcon: IconButton(onPressed: _load, icon: const Icon(Icons.arrow_forward)), border: const OutlineInputBorder()))), Expanded(child: _loading ? const Center(child: CircularProgressIndicator()) : RefreshIndicator(onRefresh: _load, child: _courses.isEmpty ? const _EmptyState(icon: Icons.menu_book_outlined, text: 'No courses match your search.') : ListView.builder(padding: const EdgeInsets.all(16), itemCount: _courses.length, itemBuilder: (_, index) { final course = _courses[index]; return Card(margin: const EdgeInsets.only(bottom: 12), child: ListTile(isThreeLine: true, leading: const CircleAvatar(child: Icon(Icons.school)), title: Text('${course['title'] ?? 'Untitled course'}'), subtitle: Text('${course['category'] ?? 'General'}  |  ${course['level'] ?? 'All levels'}\n${course['description'] ?? 'Explore this learning path.'}'), trailing: const Icon(Icons.chevron_right), onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => CourseDetailScreen(apiService: widget.apiService, course: course)))); })))]);
+  @override void dispose() { _search.dispose(); super.dispose(); }
+}

class CourseDetailScreen extends StatefulWidget { const CourseDetailScreen({required this.apiService, required this.course, super.key}); final ApiService apiService; final Map<String, dynamic> course; @override State<CourseDetailScreen> createState() => _CourseDetailScreenState(); }
class _CourseDetailScreenState extends State<CourseDetailScreen> { bool _busy = false; Future<void> _enroll() async { setState(() => _busy = true); try { await widget.apiService.enroll(int.parse('${widget.course['id']}')); if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('You are enrolled in this course.'))); } catch (error) { if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error.toString()))); } finally { if (mounted) setState(() => _busy = false); } } @override Widget build(BuildContext context) => Scaffold(appBar: AppBar(title: const Text('Course overview')), body: ListView(padding: const EdgeInsets.all(20), children: [Text('${widget.course['title'] ?? 'Course'}', style: Theme.of(context).textTheme.headlineSmall), const SizedBox(height: 8), Text('${widget.course['category'] ?? ''}  |  ${widget.course['level'] ?? ''}  |  ${widget.course['duration'] ?? 'Self paced'}'), const SizedBox(height: 20), Text('${widget.course['description'] ?? 'Build practical skills through guided lessons and projects.'}'), const SizedBox(height: 24), FilledButton.icon(onPressed: _busy ? null : _enroll, icon: const Icon(Icons.add_task), label: Text(_busy ? 'Enrolling...' : 'Enroll now')), const SizedBox(height: 24), Text('Learning path', style: Theme.of(context).textTheme.titleLarge), const _LessonTile(title: 'Module 1: Foundations', icon: Icons.play_circle_outline), const _LessonTile(title: 'Module 2: Practice lab', icon: Icons.science_outlined), const _LessonTile(title: 'Checkpoint quiz', icon: Icons.quiz_outlined, quiz: true)])); }
class _LessonTile extends StatelessWidget { const _LessonTile({required this.title, required this.icon, this.quiz = false}); final String title; final IconData icon; final bool quiz; @override Widget build(BuildContext context) => Card(child: ListTile(leading: Icon(icon), title: Text(title), trailing: quiz ? IconButton(icon: const Icon(Icons.chevron_right), onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => QuizScreen(title: title)))) : const Icon(Icons.lock_open_outlined))); }

class AssignmentsScreen extends StatefulWidget { const AssignmentsScreen({required this.apiService, required this.initialItems, super.key}); final ApiService apiService; final List<Map<String, dynamic>> initialItems; @override State<AssignmentsScreen> createState() => _AssignmentsScreenState(); }
class _AssignmentsScreenState extends State<AssignmentsScreen> { late List<Map<String, dynamic>> _items; bool _loading = false; @override void initState() { super.initState(); _items = widget.initialItems; } Future<void> _load() async { setState(() => _loading = true); try { if (_items.isEmpty) { final data = await widget.apiService.fetchAssignments(courseId: 1); if (mounted) setState(() => _items = data); } } catch (error) { if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error.toString()))); } finally { if (mounted) setState(() => _loading = false); } } @override Widget build(BuildContext context) => _loading ? const Center(child: CircularProgressIndicator()) : RefreshIndicator(onRefresh: _load, child: _items.isEmpty ? const _EmptyState(icon: Icons.assignment_outlined, text: 'Your assignments will appear here.') : ListView(padding: const EdgeInsets.all(16), children: _items.map((item) => Card(child: ListTile(title: Text('${item['title'] ?? 'Assignment'}'), subtitle: Text('Due ${item['due_date'] ?? item['dueDate'] ?? 'soon'}\n${item['status'] ?? 'Open'}'), isThreeLine: true, trailing: const Icon(Icons.chevron_right), onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => AssignmentScreen(apiService: widget.apiService, assignment: item))))).toList())); } }
class AssignmentScreen extends StatefulWidget { const AssignmentScreen({required this.apiService, required this.assignment, super.key}); final ApiService apiService; final Map<String, dynamic> assignment; @override State<AssignmentScreen> createState() => _AssignmentScreenState(); }
class _AssignmentScreenState extends State<AssignmentScreen> { final _text = TextEditingController(); bool _busy = false; @override void dispose() { _text.dispose(); super.dispose(); } Future<void> _submit() async { if (_text.text.trim().isEmpty) return; setState(() => _busy = true); try { await widget.apiService.submitAssignment(int.parse('${widget.assignment['id']}'), _text.text.trim()); if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Assignment submitted.'))); } catch (error) { if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error.toString()))); } finally { if (mounted) setState(() => _busy = false); } } @override Widget build(BuildContext context) => Scaffold(appBar: AppBar(title: const Text('Assignment')), body: ListView(padding: const EdgeInsets.all(20), children: [Text('${widget.assignment['title'] ?? 'Assignment'}', style: Theme.of(context).textTheme.headlineSmall), const SizedBox(height: 12), Text('${widget.assignment['description'] ?? 'Complete the task and share your response.'}'), const SizedBox(height: 20), TextField(controller: _text, minLines: 6, maxLines: 10, decoration: const InputDecoration(labelText: 'Your submission', alignLabelWithHint: true, border: OutlineInputBorder())), const SizedBox(height: 16), FilledButton.icon(onPressed: _busy ? null : _submit, icon: const Icon(Icons.send), label: Text(_busy ? 'Submitting...' : 'Submit assignment'))])); }

class NotificationsScreen extends StatelessWidget { const NotificationsScreen({required this.apiService, super.key}); final ApiService apiService; @override Widget build(BuildContext context) => FutureBuilder<List<Map<String, dynamic>>>(future: apiService.fetchNotifications(), builder: (_, snapshot) { if (!snapshot.hasData) return const Center(child: CircularProgressIndicator()); final items = snapshot.data!; return items.isEmpty ? const _EmptyState(icon: Icons.notifications_none, text: 'You are all caught up.') : ListView(padding: const EdgeInsets.all(16), children: items.map((item) => Card(child: ListTile(leading: const CircleAvatar(child: Icon(Icons.notifications)), title: Text('${item['text'] ?? item['message'] ?? 'New update'}'), subtitle: Text('${item['time'] ?? 'Recently'}')))).toList()); }); }
class ProfileScreen extends StatelessWidget { const ProfileScreen({required this.apiService, required this.user, required this.onLogout, required this.isDarkMode, required this.onThemeChanged, super.key}); final ApiService apiService; final Map<String, dynamic> user; final VoidCallback onLogout; final bool isDarkMode; final ValueChanged<bool> onThemeChanged; @override Widget build(BuildContext context) => ListView(padding: const EdgeInsets.all(20), children: [const CircleAvatar(radius: 38, child: Icon(Icons.person, size: 42)), const SizedBox(height: 12), Text('${user['name'] ?? 'Learner'}', textAlign: TextAlign.center, style: Theme.of(context).textTheme.headlineSmall), Text('${user['email'] ?? ''}', textAlign: TextAlign.center), const SizedBox(height: 24), Card(child: ListTile(leading: const Icon(Icons.workspace_premium_outlined), title: const Text('Certificates'), trailing: const Icon(Icons.chevron_right), onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => CertificatesScreen(apiService: apiService)))), Card(child: SwitchListTile(secondary: const Icon(Icons.dark_mode_outlined), title: const Text('Dark mode'), value: isDarkMode, onChanged: onThemeChanged)), Card(child: ListTile(leading: const Icon(Icons.settings_outlined), title: const Text('Account settings'), subtitle: Text('Role: ${user['role'] ?? 'student'}'))), const SizedBox(height: 18), OutlinedButton.icon(onPressed: () async { await apiService.logout(); onLogout(); }, icon: const Icon(Icons.logout), label: const Text('Sign out'))]); }

class GlobalSearchDelegate extends SearchDelegate<void> {
  GlobalSearchDelegate(this.apiService) : super(searchFieldLabel: 'Search courses, tasks, and quizzes');
  final ApiService apiService;
  @override List<Widget>? buildActions(BuildContext context) => [if (query.isNotEmpty) IconButton(onPressed: () => query = '', icon: const Icon(Icons.clear), tooltip: 'Clear search')];
  @override Widget? buildLeading(BuildContext context) => IconButton(onPressed: close, icon: const Icon(Icons.arrow_back), tooltip: 'Close search');
  @override Widget buildResults(BuildContext context) => _results();
  @override Widget buildSuggestions(BuildContext context) => _results();
  Widget _results() {
    if (query.trim().isEmpty) return const _EmptyState(icon: Icons.search, text: 'Search across your learning content.');
    return FutureBuilder<List<Map<String, dynamic>>>(future: apiService.fetchCourses(search: query.trim()), builder: (context, snapshot) {
      if (snapshot.hasError) return _EmptyState(icon: Icons.cloud_off, text: 'Search is unavailable. Try again.');
      if (!snapshot.hasData) return const Center(child: CircularProgressIndicator());
      final results = snapshot.data!;
      if (results.isEmpty) return const _EmptyState(icon: Icons.search_off, text: 'No matching content found.');
      return ListView.builder(padding: const EdgeInsets.all(16), itemCount: results.length, itemBuilder: (context, index) { final item = results[index]; return Card(child: ListTile(leading: const Icon(Icons.menu_book), title: Text('${item['title'] ?? 'Course'}'), subtitle: Text('${item['category'] ?? ''}  |  ${item['level'] ?? ''}'), onTap: () { close(context, null); Navigator.push(context, MaterialPageRoute(builder: (_) => CourseDetailScreen(apiService: apiService, course: item))); })); });
    });
  }
}
class CertificatesScreen extends StatelessWidget { const CertificatesScreen({required this.apiService, super.key}); final ApiService apiService; @override Widget build(BuildContext context) => Scaffold(appBar: AppBar(title: const Text('Certificates')), body: FutureBuilder<List<Map<String, dynamic>>>(future: apiService.fetchCertificates(), builder: (_, snapshot) { if (!snapshot.hasData) return const Center(child: CircularProgressIndicator()); final items = snapshot.data!; return items.isEmpty ? const _EmptyState(icon: Icons.workspace_premium_outlined, text: 'Complete a course to earn your first certificate.') : ListView(padding: const EdgeInsets.all(16), children: items.map((item) => Card(child: ListTile(leading: const Icon(Icons.workspace_premium, color: Colors.amber), title: Text('${item['name'] ?? item['title'] ?? 'Certificate'}'), subtitle: Text('${item['status'] ?? 'Issued'}  ${item['verification_code'] ?? ''}'), trailing: const Icon(Icons.download_outlined)))).toList()); })); }

class QuizScreen extends StatefulWidget { const QuizScreen({required this.title, super.key}); final String title; @override State<QuizScreen> createState() => _QuizScreenState(); }
class _QuizScreenState extends State<QuizScreen> { int _selected = -1; bool _submitted = false; @override Widget build(BuildContext context) => Scaffold(appBar: AppBar(title: Text(widget.title)), body: ListView(padding: const EdgeInsets.all(20), children: [Text('Checkpoint quiz', style: Theme.of(context).textTheme.headlineSmall), const SizedBox(height: 8), const Text('Test your understanding before moving to the next module.'), const SizedBox(height: 24), const Text('Which habit best supports steady learning?', style: TextStyle(fontWeight: FontWeight.bold)), ...['Review a little each day', 'Wait until the deadline', 'Skip practice work', 'Only read the summary'].asMap().entries.map((entry) => RadioListTile<int>(value: entry.key, groupValue: _selected, title: Text(entry.value), onChanged: _submitted ? null : (value) => setState(() => _selected = value!))), const SizedBox(height: 16), FilledButton(onPressed: _selected < 0 || _submitted ? null : () => setState(() => _submitted = true), child: Text(_submitted ? 'Answer recorded' : 'Submit answer')), if (_submitted) const Padding(padding: EdgeInsets.only(top: 16), child: Text('Correct. Keep the momentum going.', style: TextStyle(color: Colors.green, fontWeight: FontWeight.bold))) ])); }
class _EmptyState extends StatelessWidget { const _EmptyState({required this.icon, required this.text}); final IconData icon; final String text; @override Widget build(BuildContext context) => Center(child: Padding(padding: const EdgeInsets.all(32), child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [Icon(icon, size: 48, color: Theme.of(context).colorScheme.primary), const SizedBox(height: 12), Text(text, textAlign: TextAlign.center)]))); }
