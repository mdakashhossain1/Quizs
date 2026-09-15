import 'package:flutter/material.dart';

import 'ads/test_ads.dart';
import 'quizs_app.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  TestAds.start();
  runApp(const QuizsApp());
}
