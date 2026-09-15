import 'dart:io';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:quizs/quizs_app.dart';
import 'package:quizs/ads/banner_ad_slot.dart';
import 'package:quizs/widgets/design_widgets.dart';

Future<void> loadFonts() async {
  final fonts = <String, List<String>>{
    'Poppins': [
      'Poppins-Light.ttf',
      'Poppins-Regular.ttf',
      'Poppins-Medium.ttf',
      'Poppins-SemiBold.ttf',
      'Poppins-Bold.ttf',
      'Poppins-ExtraBold.ttf',
    ],
    'Quizlo': ['Quizlo-DEMO.otf'],
    'HappySchool': ['Happy-School.ttf'],
    'DaysOne': ['DaysOne-Regular.ttf'],
    'Quicksand': ['Quicksand.ttf'],
    'Questrial': ['Questrial-Regular.ttf'],
  };
  for (final entry in fonts.entries) {
    final loader = FontLoader(entry.key);
    for (final file in entry.value) {
      loader.addFont(rootBundle.load('assets/fonts/$file'));
    }
    await loader.load();
  }
}

Future<void> settleImages(WidgetTester tester) async {
  await tester.runAsync(() async {
    final context = tester.element(find.byType(DesignCanvas).last);
    for (final file in Directory('assets/figma').listSync().whereType<File>()) {
      if (file.path.endsWith('.png')) {
        await precacheImage(
          AssetImage(file.path.replaceAll('\\', '/')),
          context,
        );
      }
    }
  });
  await tester.pumpAndSettle();
}

Future<void> tapAction(WidgetTester tester, String name) async {
  final finder = find
      .byWidgetPredicate(
        (widget) => widget is DesignAction && widget.label == name,
      )
      .first;
  await tester.ensureVisible(finder);
  await tester.tap(finder);
  await tester.pumpAndSettle();
}

void main() {
  setUpAll(loadFonts);

  testWidgets('Static navigation reaches the supplied screens', (tester) async {
    tester.view.physicalSize = const Size(412, 917);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);
    await tester.pumpWidget(const QuizsApp());
    await settleImages(tester);
    expect(find.text('Welcome'), findsOneWidget);
    await tapAction(tester, 'Browse categories');
    expect(find.text('Choose category'), findsOneWidget);
    await tapAction(tester, 'Maths quizzes');
    expect(find.text('Mathematics'), findsOneWidget);
    await tapAction(tester, 'Open Trigonometry quiz');
    expect(find.text('Mars'), findsOneWidget);
    await tapAction(tester, 'Next question');
    expect(find.text('Mars- The red planet'), findsOneWidget);
    await tapAction(tester, 'Next Trial');
    expect(find.text('Congratulations !'), findsOneWidget);
    await tapAction(tester, 'Return home');
    await tapAction(tester, 'Achievements');
    expect(find.text('132'), findsOneWidget);
    await tapAction(tester, 'Back');
    expect(find.text('Welcome'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });

  const routes = {
    'home': '/',
    'categories': '/categories',
    'mathematics': '/mathematics',
    'achievements': '/achievements',
    'question': '/question',
    'explanation': '/explanation',
    'results': '/results',
  };

  for (final entry in routes.entries) {
    testWidgets('${entry.key} renders at Figma size', (tester) async {
      debugDisableShadows = false;
      tester.view.physicalSize = const Size(412, 917);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);
      await tester.pumpWidget(QuizsApp(initialRoute: entry.value));
      await settleImages(tester);
      expect(tester.takeException(), isNull);
      final banner = find.byType(BannerAdSlot);
      expect(banner, findsOneWidget);
      expect(tester.getSize(banner).height, BannerAdSlot.height);
      expect(
        find.ancestor(of: banner, matching: find.byType(FittedBox)),
        findsNothing,
      );
      final boundary = tester.renderObject<RenderRepaintBoundary>(
        find.byKey(const ValueKey('design-canvas')).last,
      );
      await tester.runAsync(() async {
        final image = await boundary.toImage();
        final bytes = await image.toByteData(format: ui.ImageByteFormat.png);
        final file = File('design/implementation/${entry.key}.png');
        await file.parent.create(recursive: true);
        await file.writeAsBytes(bytes!.buffer.asUint8List());
        image.dispose();
      });
      debugDisableShadows = true;
    });
  }

  testWidgets('Narrow phones can scroll to navigation without overflow', (
    tester,
  ) async {
    tester.view.physicalSize = const Size(360, 640);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);
    await tester.pumpWidget(const QuizsApp());
    await settleImages(tester);
    final banner = find.byType(BannerAdSlot);
    expect(tester.getSize(banner).height, BannerAdSlot.height);
    final canvas = tester.widget<DesignCanvas>(find.byType(DesignCanvas));
    expect(
      tester.getBottomRight(banner).dy + 12,
      lessThanOrEqualTo(canvas.adBefore! * 360 / 412 + 0.01),
    );
    await tapAction(tester, 'Browse categories');
    expect(find.text('Choose category'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });
}
