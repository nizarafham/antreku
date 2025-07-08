import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'screens/login_screen.dart';
import 'screens/dashboard_screen.dart';
import 'screens/splash_screen.dart';

void main() {
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (ctx) => AuthProvider(),
      child: MaterialApp(
        title: 'AntreKu Owner',
        theme: ThemeData(
          primarySwatch: Colors.blue,
          visualDensity: VisualDensity.adaptivePlatformDensity,
        ),
        home: Consumer<AuthProvider>(
          builder: (ctx, auth, _) {
            // Jika masih loading (checking authentication)
            if (auth.isLoading) {
              return SplashScreen();
            }
            // Jika sudah terautentikasi, ke Dashboard
            if (auth.isAuthenticated) {
              return DashboardScreen();
            }
            // Jika tidak terautentikasi, ke Login
            return LoginScreen();
          }
        ),
      ),
    );
  }
}