import 'dart:convert';

import 'package:http/http.dart' as http;

class ApiService {
  static const String _baseUrl = 'http://10.0.2.2:8000/api.php';
  final http.Client _client;
  String? _sessionCookie;
  String? _csrfToken;

  ApiService({http.Client? client}) : _client = client ?? http.Client();

  Map<String, String> get _headers => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (_sessionCookie != null) 'Cookie': _sessionCookie!,
        if (_csrfToken != null) 'X-CSRF-Token': _csrfToken!,
      };

  void _rememberSession(http.Response response) {
    final setCookie = response.headers['set-cookie'];
    if (setCookie != null) {
      _sessionCookie = setCookie.split(';').first;
    }
  }

  Future<Map<String, dynamic>> _request(
    String action, {
    String method = 'GET',
    Map<String, dynamic>? body,
    Map<String, String>? query,
  }) async {
    final uri = Uri.parse('$_baseUrl?action=$action').replace(queryParameters: {
      'action': action,
      ...?query,
    });
    final response = method == 'POST'
        ? await _client.post(uri,
            headers: _headers, body: jsonEncode(body ?? {}))
        : await _client.get(uri, headers: _headers);
    _rememberSession(response);
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;
    if (decoded['success'] != true) {
      throw Exception(decoded['message'] ?? 'Request failed');
    }
    return Map<String, dynamic>.from(decoded['data'] ?? {});
  }

  Future<void> _loadCsrfToken() async {
    final data = await _request('csrf-token');
    _csrfToken = data['csrf_token']?.toString();
  }

  Future<Map<String, dynamic>> login(
      String email, String password, String role) async {
    await _loadCsrfToken();
    return _request('login', method: 'POST', query: {
      'role': role
    }, body: {
      'email': email,
      'password': password,
    });
  }

  Future<Map<String, dynamic>> register({
    required String firstName,
    required String lastName,
    required String email,
    required String password,
    required String role,
  }) async {
    await _loadCsrfToken();
    return _request('register', method: 'POST', body: {
      'first_name': firstName,
      'last_name': lastName,
      'email': email,
      'password': password,
      'role': role,
    });
  }

  Future<Map<String, dynamic>> currentUser() => _request('me');

  Future<void> logout() async {
    await _request('logout', method: 'POST');
    _sessionCookie = null;
    _csrfToken = null;
  }

  Future<Map<String, dynamic>> fetchDashboard(String role) async {
    return _request('dashboard', query: {'role': role});
  }

  Future<List<Map<String, dynamic>>> fetchCourses({String search = ''}) async {
    final data = await _request('courses',
        query: {'limit': '50', if (search.isNotEmpty) 'search': search});
    return _maps(data['courses']);
  }

  Future<Map<String, dynamic>> fetchCourse(int id) =>
      _request('course', query: {'id': '$id'});

  Future<void> enroll(int courseId) async =>
      _request('enroll', method: 'POST', body: {'course_id': courseId});

  Future<List<Map<String, dynamic>>> fetchAssignments({int? courseId}) async {
    final data = await _request('assignments',
        query: {if (courseId != null) 'course_id': '$courseId'});
    return _maps(data['assignments']);
  }

  Future<Map<String, dynamic>> fetchAssignment(int id) =>
      _request('assignment', query: {'id': '$id'});

  Future<void> submitAssignment(int id, String text) async => _request(
        'submit-assignment',
        method: 'POST',
        body: {'assignment_id': id, 'submission_text': text},
      );

  Future<List<Map<String, dynamic>>> fetchNotifications() async {
    final data = await _request('notifications');
    return _maps(data['notifications']);
  }

  Future<List<Map<String, dynamic>>> fetchCertificates() async {
    final data = await _request('certificates');
    return _maps(data['certificates']);
  }

  Future<Map<String, dynamic>> verifyCertificate(String id) =>
      _request('certificate', query: {'id': id});

  Future<List<Map<String, dynamic>>> fetchSchedule() async {
    final data = await _request('schedule');
    return _maps(data['schedule']);
  }

  Future<List<Map<String, dynamic>>> fetchDiscussions() async {
    final data = await _request('forum');
    return _maps(data['threads']);
  }

  Future<List<Map<String, dynamic>>> fetchAdminUsers() async =>
      _maps((await _request('admin-users', query: {'page': '1'}))['users']);
  Future<List<Map<String, dynamic>>> fetchAdminCourses() async =>
      _maps((await _request('admin-courses'))['courses']);
  Future<List<Map<String, dynamic>>> fetchAdminPayments() async =>
      _maps((await _request('admin-payments'))['payments']);
  Future<Map<String, dynamic>> fetchAdminReport() async =>
      (await _request('admin-reports'))['report'] as Map<String, dynamic>? ??
      {};
  Future<List<Map<String, dynamic>>> fetchAuditHistory() async =>
      _maps((await _request('admin-audit'))['entries']);
  Future<void> deleteUser(int userId) async =>
      _request('delete-user', method: 'POST', body: {'user_id': userId});

  Future<List<Map<String, dynamic>>> fetchInstructorCourses() async =>
      _maps((await _request('instructor-courses'))['courses']);
  Future<List<Map<String, dynamic>>> fetchInstructorStudents(
          int courseId) async =>
      _maps((await _request('instructor-students',
          query: {'course_id': '$courseId'}))['students']);
  Future<List<Map<String, dynamic>>> fetchInstructorResources(
          int courseId) async =>
      _maps((await _request('instructor-resources',
          query: {'course_id': '$courseId'}))['resources']);
  Future<List<Map<String, dynamic>>> fetchInstructorAssignments(
          int courseId) async =>
      _maps((await _request('instructor-assignments',
          query: {'course_id': '$courseId'}))['assignments']);
  Future<List<Map<String, dynamic>>> fetchGradingSubmissions() async =>
      _maps((await _request('grading-submissions',
          query: {'page': '1', 'status': 'pending'}))['submissions']);
  Future<void> createCourse(Map<String, dynamic> data) async =>
      _request('create-course', method: 'POST', body: data);
  Future<void> createResource(Map<String, dynamic> data) async =>
      _request('create-resource', method: 'POST', body: data);
  Future<void> createAssignment(Map<String, dynamic> data) async =>
      _request('create-assignment', method: 'POST', body: data);
  Future<void> gradeSubmission(int id, double score, String feedback) async =>
      _request('grade-submission',
          method: 'POST',
          body: {'submission_id': id, 'score': score, 'feedback': feedback});
  Future<List<Map<String, dynamic>>> fetchStudentProgress(
          {int? courseId}) async =>
      _maps((await _request('student-progress',
          query: {'course_id': '${courseId ?? 0}'}))['progress']);
  Future<List<Map<String, dynamic>>> fetchGrades({int? courseId}) async =>
      _maps((await _request('student-grades',
          query: {'course_id': '${courseId ?? 0}'}))['grades']);
  Future<List<Map<String, dynamic>>> fetchMyCourses() async =>
      _maps((await _request('my-courses'))['enrollments']);
  Future<Map<String, dynamic>?> fetchLessonProgress(
          int courseId, int resourceId) async =>
      (await _request('lesson-progress', query: {
        'course_id': '$courseId',
        'resource_id': '$resourceId'
      }))['progress'] as Map<String, dynamic>?;
  Future<Map<String, dynamic>> trackLesson(
          {required int courseId,
          required int resourceId,
          required double percentage,
          required double position,
          required bool completed}) async =>
      _request('track-lesson', method: 'POST', body: {
        'course_id': courseId,
        'resource_id': resourceId,
        'percentage': percentage,
        'position': position,
        'completed': completed
      });

  static List<Map<String, dynamic>> _maps(dynamic value) => value is List
      ? value.map((item) => Map<String, dynamic>.from(item as Map)).toList()
      : <Map<String, dynamic>>[];
}
