import 'package:flutter/material.dart';

import '../services/api_service.dart';

class AuthScreen extends StatefulWidget {
  const AuthScreen(
      {required this.apiService, required this.onAuthenticated, super.key});

  final ApiService apiService;
  final ValueChanged<Map<String, dynamic>> onAuthenticated;

  @override
  State<AuthScreen> createState() => _AuthScreenState();
}

class _AuthScreenState extends State<AuthScreen> {
  final _formKey = GlobalKey<FormState>();
  final _email = TextEditingController(text: 'student@learnflow.app');
  final _password = TextEditingController(text: 'student123');
  final _firstName = TextEditingController();
  final _lastName = TextEditingController();
  bool _registering = false;
  bool _busy = false;
  String _role = 'student';

  @override
  void dispose() {
    _email.dispose();
    _password.dispose();
    _firstName.dispose();
    _lastName.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _busy = true);
    try {
      if (_registering) {
        await widget.apiService.register(
          firstName: _firstName.text.trim(),
          lastName: _lastName.text.trim(),
          email: _email.text.trim(),
          password: _password.text,
          role: _role,
        );
      }
      final data = await widget.apiService
          .login(_email.text.trim(), _password.text, _role);
      widget.onAuthenticated(Map<String, dynamic>.from(data['user'] ?? {}));
    } catch (error) {
      if (mounted)
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(error.toString())));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(28),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 420),
              child: Form(
                key: _formKey,
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const Icon(Icons.auto_stories,
                          size: 56, color: Color(0xFF4F46E5)),
                      const SizedBox(height: 16),
                      Text(
                          _registering
                              ? 'Create your learning account'
                              : 'Welcome back',
                          style: Theme.of(context).textTheme.headlineSmall,
                          textAlign: TextAlign.center),
                      const SizedBox(height: 8),
                      Text(
                          _registering
                              ? 'Start building your next skill.'
                              : 'Continue where your learning left off.',
                          textAlign: TextAlign.center),
                      const SizedBox(height: 28),
                      if (_registering) ...[
                        Row(children: [
                          Expanded(
                              child: TextFormField(
                                  controller: _firstName,
                                  decoration: const InputDecoration(
                                      labelText: 'First name',
                                      border: OutlineInputBorder()),
                                  validator: _required)),
                          const SizedBox(width: 12),
                          Expanded(
                              child: TextFormField(
                                  controller: _lastName,
                                  decoration: const InputDecoration(
                                      labelText: 'Last name',
                                      border: OutlineInputBorder()),
                                  validator: _required)),
                        ]),
                        const SizedBox(height: 12),
                      ],
                      TextFormField(
                          controller: _email,
                          keyboardType: TextInputType.emailAddress,
                          decoration: const InputDecoration(
                              labelText: 'Email',
                              border: OutlineInputBorder(),
                              prefixIcon: Icon(Icons.mail_outline)),
                          validator: (value) =>
                              value != null && value.contains('@')
                                  ? null
                                  : 'Enter a valid email'),
                      const SizedBox(height: 12),
                      TextFormField(
                          controller: _password,
                          obscureText: true,
                          decoration: const InputDecoration(
                              labelText: 'Password',
                              border: OutlineInputBorder(),
                              prefixIcon: Icon(Icons.lock_outline)),
                          validator: (value) =>
                              value != null && value.length >= 8
                                  ? null
                                  : 'Use at least 8 characters'),
                      const SizedBox(height: 12),
                      DropdownButtonFormField<String>(
                          value: _role,
                          decoration: const InputDecoration(
                              labelText: 'Account type',
                              border: OutlineInputBorder()),
                          items: const [
                            DropdownMenuItem(
                                value: 'student', child: Text('Student')),
                            DropdownMenuItem(
                                value: 'instructor', child: Text('Instructor')),
                            DropdownMenuItem(
                                value: 'admin', child: Text('Administrator'))
                          ],
                          onChanged: (value) => setState(() => _role = value!)),
                      const SizedBox(height: 20),
                      FilledButton.icon(
                          onPressed: _busy ? null : _submit,
                          icon: _busy
                              ? const SizedBox.square(
                                  dimension: 18,
                                  child:
                                      CircularProgressIndicator(strokeWidth: 2))
                              : const Icon(Icons.arrow_forward),
                          label: Text(
                              _registering ? 'Create account' : 'Sign in')),
                      TextButton(
                          onPressed: _busy
                              ? null
                              : () =>
                                  setState(() => _registering = !_registering),
                          child: Text(_registering
                              ? 'Already have an account? Sign in'
                              : 'New to LearnFlow? Create an account')),
                    ]),
              ),
            ),
          ),
        ),
      ),
    );
  }

  String? _required(String? value) =>
      value == null || value.trim().isEmpty ? 'Required' : null;
}
