import 'dart:convert';

import 'package:http/http.dart' as http;

class ApiService {
  static const String _baseUrl = 'http://10.0.2.2:8000/api.php';

  Future<Map<String, dynamic>> fetchDashboard(String role) async {
    final response = await http.get(
      Uri.parse('$_baseUrl?action=dashboard&role=$role'),
    );

    if (response.statusCode != 200) {
      throw Exception('Failed to load dashboard: ${response.statusCode}');
    }

    final body = jsonDecode(response.body);
    if (body['success'] != true) {
      throw Exception(body['message'] ?? 'Dashboard request failed');
    }

    return Map<String, dynamic>.from(body['data'] ?? {});
  }
}
