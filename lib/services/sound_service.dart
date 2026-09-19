import 'package:audioplayers/audioplayers.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// One-shot sound effects (tap/correct/wrong/victory) gated by the user's
/// sound toggle in the profile screen. The toggle persists across app
/// restarts via [SharedPreferences], matching [_enabledKey].
class SoundService {
  SoundService._();
  static final SoundService instance = SoundService._();

  static const _enabledKey = 'sound_enabled';

  final AudioPlayer _player = AudioPlayer()..audioCache.prefix = 'assets/sounds/';
  bool _enabled = true;
  bool _loaded = false;

  Future<void> _ensureLoaded() async {
    if (_loaded) return;
    _loaded = true;
    final prefs = await SharedPreferences.getInstance();
    _enabled = prefs.getBool(_enabledKey) ?? true;
  }

  Future<bool> isEnabled() async {
    await _ensureLoaded();
    return _enabled;
  }

  Future<void> setEnabled(bool value) async {
    _enabled = value;
    _loaded = true;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_enabledKey, value);
  }

  Future<void> _play(String fileName) async {
    await _ensureLoaded();
    if (!_enabled) return;
    try {
      await _player.stop();
      await _player.play(AssetSource(fileName));
    } catch (_) {
      // Best-effort — a missing audio device/asset should never crash the quiz flow.
    }
  }

  Future<void> playClick() => _play('click.wav');
  Future<void> playCorrect() => _play('correct.wav');
  Future<void> playWrong() => _play('wrong.wav');
  Future<void> playTick() => _play('tick.wav');
  Future<void> playVictory() => _play('victory.wav');
}
