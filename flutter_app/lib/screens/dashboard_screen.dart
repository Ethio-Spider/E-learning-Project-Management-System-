import 'package:flutter/material.dart';

import '../services/api_service.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final ApiService _apiService = ApiService();
  String _selectedRole = 'student';
  Map<String, dynamic> _data = {};
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadDashboard();
  }

  Future<void> _loadDashboard() async {
    setState(() => _isLoading = true);

    try {
      final dashboardData = await _apiService.fetchDashboard(_selectedRole);
      setState(() {
        _data = dashboardData;
        _isLoading = false;
      });
    } catch (error) {
      setState(() => _isLoading = false);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(error.toString())),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final stats = List<Map<String, dynamic>>.from((_data['stats'] ?? []) as List);
    final courses = List<Map<String, dynamic>>.from((_data['courses'] ?? []) as List);
    final assignments = List<Map<String, dynamic>>.from((_data['assignments'] ?? []) as List);

    return Scaffold(
      appBar: AppBar(
        title: const Text('LearnFlow Pro'),
        actions: [
          SegmentedButton<String>(
            segments: const [
              ButtonSegment(value: 'student', label: Text('Student')),
              ButtonSegment(value: 'instructor', label: Text('Instructor')),
              ButtonSegment(value: 'admin', label: Text('Admin')),
            ],
            selected: {_selectedRole},
            onSelectionChanged: (selection) {
              setState(() => _selectedRole = selection.first);
              _loadDashboard();
            },
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadDashboard,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  _buildHeaderCard(),
                  const SizedBox(height: 18),
                  GridView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      mainAxisSpacing: 12,
                      crossAxisSpacing: 12,
                      childAspectRatio: 1.5,
                    ),
                    itemCount: stats.length,
                    itemBuilder: (context, index) {
                      final stat = stats[index];
                      return Card(
                        child: Padding(
                          padding: const EdgeInsets.all(14),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                stat['label'] ?? '',
                                style: Theme.of(context).textTheme.labelMedium,
                              ),
                              Text(
                                stat['value'] ?? '',
                                style: Theme.of(context).textTheme.headlineSmall,
                              ),
                              Text(
                                stat['trend'] ?? '',
                                style: const TextStyle(color: Colors.green),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                  const SizedBox(height: 18),
                  _buildSectionTitle('Courses'),
                  ...courses.map((course) => Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        child: ListTile(
                          title: Text(course['title'] ?? ''),
                          subtitle: Text('${course['category'] ?? ''} • ${course['level'] ?? ''}'),
                          trailing: Text('${course['progress'] ?? 0}%'),
                        ),
                      )),
                  const SizedBox(height: 18),
                  _buildSectionTitle('Assignments'),
                  ...assignments.map((assignment) => Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        child: ListTile(
                          title: Text(assignment['title'] ?? ''),
                          subtitle: Text(assignment['course'] ?? ''),
                          trailing: Text(assignment['status'] ?? ''),
                        ),
                      )),
                ],
              ),
            ),
    );
  }

  Widget _buildHeaderCard() {
    return Card(
      color: const Color(0xFFE7ECFF),
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Learning momentum', style: TextStyle(fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  Text(
                    'Stay consistent and complete your next milestone before Friday.',
                    style: Theme.of(context).textTheme.bodyMedium,
                  ),
                ],
              ),
            ),
            ElevatedButton(
              onPressed: _loadDashboard,
              child: const Text('Refresh'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Text(
        title,
        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
      ),
    );
  }
}
