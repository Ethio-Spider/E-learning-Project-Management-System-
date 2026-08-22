import 'package:flutter/material.dart';

import 'screens/dashboard_screen.dart';

void main() {
  runApp(const LearnFlowApp());
}

class LearnFlowApp extends StatelessWidget {
  const LearnFlowApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'LearnFlow Pro',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF4F46E5)),
        useMaterial3: true,
      ),
      home: const DashboardScreen(),
    );
  }
}
