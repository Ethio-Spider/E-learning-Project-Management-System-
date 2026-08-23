import 'package:flutter/material.dart';

import 'screens/auth_screen.dart';
import 'screens/admin_screen.dart';
import 'screens/instructor_screen.dart';
import 'screens/dashboard_screen.dart';
import 'services/api_service.dart';

void main() {
  runApp(const LearnFlowApp());
}

class LearnFlowApp extends StatefulWidget {
  const LearnFlowApp({super.key});

  @override
  State<LearnFlowApp> createState() => _LearnFlowAppState();
}

class _LearnFlowAppState extends State<LearnFlowApp> {
  ThemeMode _themeMode = ThemeMode.light;

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'LearnFlow Pro',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF245C4A)),
        scaffoldBackgroundColor: const Color(0xFFF8FAF8),
        useMaterial3: true,
      ),
      darkTheme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
            seedColor: const Color(0xFF6BC2A4), brightness: Brightness.dark),
        useMaterial3: true,
      ),
      themeMode: _themeMode,
      home: AuthGate(
        apiService: ApiService(),
        isDarkMode: _themeMode == ThemeMode.dark,
        onThemeChanged: (isDark) => setState(
            () => _themeMode = isDark ? ThemeMode.dark : ThemeMode.light),
      ),
    );
  }
}

class AuthGate extends StatefulWidget {
  const AuthGate(
      {required this.apiService,
      required this.isDarkMode,
      required this.onThemeChanged,
      super.key});

  final ApiService apiService;
  final bool isDarkMode;
  final ValueChanged<bool> onThemeChanged;

  @override
  State<AuthGate> createState() => _AuthGateState();
}

class _AuthGateState extends State<AuthGate> {
  Map<String, dynamic>? _user;
  bool _checking = true;

  @override
  void initState() {
    super.initState();
    _restoreSession();
  }

  Future<void> _restoreSession() async {
    try {
      final data = await widget.apiService.currentUser();
      if (mounted)
        setState(() => _user = Map<String, dynamic>.from(data['user'] ?? {}));
    } catch (_) {
      // A missing PHP session is the normal first-launch state.
    }
    if (mounted) setState(() => _checking = false);
  }

  @override
  Widget build(BuildContext context) {
    if (_checking)
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    if (_user == null) {
      return AuthScreen(
        apiService: widget.apiService,
        onAuthenticated: (user) => setState(() => _user = user),
      );
    }
    if (_user!['role'] == 'admin') {
      return AdminScreen(
          apiService: widget.apiService,
          user: _user!,
          onLogout: () => setState(() => _user = null),
          isDarkMode: widget.isDarkMode,
          onThemeChanged: widget.onThemeChanged);
    }
    if (_user!['role'] == 'instructor') {
      return InstructorScreen(
          apiService: widget.apiService,
          user: _user!,
          onLogout: () => setState(() => _user = null),
          isDarkMode: widget.isDarkMode,
          onThemeChanged: widget.onThemeChanged);
    }
    return DashboardScreen(
      apiService: widget.apiService,
      user: _user!,
      onLogout: () => setState(() => _user = null),
      isDarkMode: widget.isDarkMode,
      onThemeChanged: widget.onThemeChanged,
    );
  }
}
