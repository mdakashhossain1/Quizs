import 'package:flutter/material.dart';

enum AppLanguageType { english, hindi }

class AppLanguage extends ChangeNotifier {
  static final AppLanguage instance = AppLanguage._();
  AppLanguage._();

  AppLanguageType _language = AppLanguageType.english;

  AppLanguageType get language => _language;
  bool get isHindi => _language == AppLanguageType.hindi;
  String get code => _language == AppLanguageType.hindi ? 'hi' : 'en';
  String get displayName => _language == AppLanguageType.hindi ? 'हिंदी' : 'English';

  void setLanguage(AppLanguageType lang) {
    if (_language != lang) {
      _language = lang;
      notifyListeners();
    }
  }

  void toggle() {
    setLanguage(
      _language == AppLanguageType.english
          ? AppLanguageType.hindi
          : AppLanguageType.english,
    );
  }
}

class AppStrings {
  AppStrings._();

  static String t(String key) {
    final lang = AppLanguage.instance.code;
    return _localizedValues[lang]?[key] ?? _localizedValues['en']?[key] ?? key;
  }

  static const Map<String, Map<String, String>> _localizedValues = {
    'en': {
      // Common & Navigation
      'home': 'Home',
      'category': 'Category',
      'dashboard': 'Category',
      'profile': 'Profile',
      'attendance': 'Attendance',
      'back': 'Back',
      'advertisement': 'ADVERTISEMENT',


      // Home Screen
      'welcome': 'Welcome',
      'leaderboard': 'LEADERBOARD',
      'achievement': 'ACHIEVEMENT',
      'rank_label': 'RANK',
      'quiz_category': 'Quiz Category',
      'browse_categories': 'Browse categories',
      'science': 'Science',
      'maths': 'Maths',
      'gk': 'Gk',
      'evs': 'Evs',
      'science_quizzes': 'Science quizzes',
      'maths_quizzes': 'Maths quizzes',
      'gk_quizzes': 'General knowledge quizzes',
      'evs_quizzes': 'Environmental studies quizzes',
      'daily_challenge': 'Daily\nChallenge',
      'join_a_quiz': 'Join a Quiz',

      // Shared quiz-card skeleton placeholder text (QuizCard in design_widgets.dart)
      'trigonometry': 'Trigonometry',

      // Profile Screen
      'level_20': '20',
      'level_label': 'Level',
      'accuracy_value': '87%',
      'accuracy_label': 'Accuracy',
      'quiz_played_stat': 'Quiz Played',
      'right_stat': 'Right',
      'wrong_stat': 'Wrong',
      'this_month_stat': 'This Month',
      'edit_profile': 'Edit Profile',
      'language': 'Language',
      'sound': 'Sound',
      'notification': 'Notification',
      'terms_conditions': 'Terms & Conditions',
      'privacy_policy': 'Privacy Policy',
      'log_out': 'Log Out',
      'select_language': 'Select Language',

      // Achievement Screen
      'achievements_title': 'Achievements',
      'your_rank_stat': 'Your Rank',
      'total_users_stat': 'Total Users',
      'active_users_stat': 'Active Users',
      'active_this_month_stat': 'Active This Month',
      'global_ranking': 'Global Ranking',
      'no_ranking_data': 'No ranking data yet.',

      // Notifications Screen
      'notifications': 'Notifications',
      'all_filter': 'All',
      'quizs_filter': 'Quizs',
      'rewards_filter': 'Rewards',
      'mark_all_read': 'Mark all read',
      'all_notifs_read': 'All notifications marked as read',
      'no_notifications': 'No notifications yet.',

      // Selection Screen
      'choose_category': 'Choose category',
      'mathematics': 'Mathematics',
      'trending_quizzes': 'Trending Quizs',
      'open_trigo': 'Open Trigonometry quiz',
      'questions_count': '10 Questions',
      'played_count': 'Played',

      // Question & Results
      'questions_header': 'Questions',
      'q_red_planet': 'Which planet is known as\nthe Red Planet?',
      'venus': 'Venus',
      'mercury': 'Mercury',
      'mars': 'Mars',
      'jupiter': 'Jupiter',
      'previous': 'Previous',
      'next': 'Next',
      'mars_desc_title': 'Mars- The red planet',
      'mars_desc_body': 'Mars is the fourth planet from the\nSun and is known as the Red Planet\nbecause of iron-rich dust on its\nsurface. It has a thin atmosphere,\nrocky terrain, giant volcanoes, deep\nvalleys, and two small moons—Phobos\nand Deimos.',
      'next_trial_in_5s': 'Next Trial in 5s',
      'next_trial': 'Next Trial',
      'congratulations': 'Congratulations !',
      'result_good': 'Great Job!',
      'result_average': 'Good Effort!',
      'result_low': 'Keep Practicing!',
      'result_accuracy_label': 'Accuracy: ',
      'result_time_label': '  •  Time: ',
      'question_solved': 'Question Solved',
      'return_home': 'Return home',

      // Authentication Screens (Sign Up, Sign In, Verify Code)
      'create_account': 'Create Account',
      'signup_subtitle': 'Fill your information below or register\nwith your social account',
      'name_label': 'Name',
      'name_placeholder': 'Ex. Aman Gupta',
      'email_label': 'Email',
      'email_placeholder': 'amangupta@gmail.com',
      'username_email_label': 'User Name/Email',
      'password_label': 'Password',
      'agree_terms': 'Agree with',
      'terms_condition_link': 'Terms & Condition',
      'sign_up_btn': 'Sign up',
      'sign_in_btn': 'Sign in',
      'or_signup_with': 'Or sign up with',
      'or_signin_with': 'Or sign in with',
      'continue_with_google': 'Continue with Google',
      'already_have_account': 'Already have an account?',
      'dont_have_account': 'Don\'t have an account?',
      'sign_in_link': 'Sign In',
      'sign_up_link': 'Sign Up',
      'welcome_back': 'Welcome Back',
      'welcome_back_subtitle': 'Ready to test your knowledge?',
      'forgot_password': 'Forgot Password?',
      'forgot_password_subtitle': 'Enter your registered email address and we will send you a verification code to reset your password.',
      'send_code_btn': 'Send Reset Code',
      'remember_password': 'Remember your password?',
      'verify_code': 'Verify Code',
      'verify_code_subtitle': 'Please enter the code we just sent to email',
      'dont_receive_otp': 'Don\'t receive OTP?',
      'resend_code': 'Resend code',
      'verify_btn': 'Verify',
      'reset_password_btn': 'Reset Password',
      // Edit Profile & Account Management
      'save_changes': 'Save Changes',
      'change_password': 'Change Password',
      'new_password': 'New Password',
      'confirm_password': 'Confirm Password',
      'delete_account': 'Delete Account',
      'delete_account_confirm_title': 'Delete Account',
      'delete_account_confirm_msg': 'Are you sure you want to permanently delete your account? This action cannot be undone.',
      'profile_updated_success': 'Profile updated successfully!',
      'password_changed_success': 'Password changed successfully!',
      'delete_btn': 'Delete',

      // Attendance Screen
      'attendance_title': 'Attendance',
      'official_attendance_log': 'Official School Attendance Log',
      'present': 'Present',
      'absent': 'Absent',
      'leave': 'Leave',
      'not_marked': 'Not Marked',
      'todays_status': "TODAY'S STATUS",
      'today': 'Today',
      'official_record': 'Official Record',
      'days_short': 'd',
      'overall_rate': 'Overall Rate',
      'great': 'Great',
      'average': 'Average',
      'low': 'Low',
      'sessions_attended': 'sessions attended',
      'of_word': 'of',
      'updated_by_admin': 'Updated by school administration',
      'attendance_log': 'Attendance Log',
      'no_attendance_recorded': 'No Attendance Recorded Yet',
      'no_attendance_sub': 'Your attendance records will appear here as your teachers mark it.',
      'official_attendance_entry': 'Official Attendance Entry',
      'could_not_load_attendance': 'Could not load attendance data.',
      'check_internet_connection': 'Please check your internet connection.',
      'retry': 'Retry',
    },
    'hi': {
      // Common & Navigation
      'home': 'होम',
      'category': 'श्रेणी',
      'dashboard': 'श्रेणी',
      'profile': 'प्रोफ़ाइल',
      'attendance': 'उपस्थिति',
      'back': 'वापस',
      'advertisement': 'विज्ञापन',


      // Home Screen
      'welcome': 'स्वागत है',
      'leaderboard': 'लीडरबोर्ड',
      'achievement': 'उपलब्धि',
      'rank_label': 'रैंक',
      'quiz_category': 'क्विज़ श्रेणी',
      'browse_categories': 'श्रेणियां देखें',
      'science': 'विज्ञान',
      'maths': 'गणित',
      'gk': 'सामान्य ज्ञान',
      'evs': 'पर्यावरण',
      'science_quizzes': 'विज्ञान क्विज़',
      'maths_quizzes': 'गणित क्विज़',
      'gk_quizzes': 'सामान्य ज्ञान क्विज़',
      'evs_quizzes': 'पर्यावरण अध्ययन क्विज़',
      'daily_challenge': 'दैनिक\nचुनौती',
      'join_a_quiz': 'क्विज़ शुरू करें',

      // Shared quiz-card skeleton placeholder text (QuizCard in design_widgets.dart)
      'trigonometry': 'त्रिकोणमिति',

      // Profile Screen
      'level_20': '20',
      'level_label': 'स्तर',
      'accuracy_value': '87%',
      'accuracy_label': 'सटीकता',
      'quiz_played_stat': 'खेले गए क्विज़',
      'right_stat': 'सही',
      'wrong_stat': 'गलत',
      'this_month_stat': 'इस महीने',
      'edit_profile': 'प्रोफ़ाइल संपादित करें',
      'language': 'भाषा',
      'sound': 'ध्वनि',
      'notification': 'सूचनाएं',
      'terms_conditions': 'नियम एवं शर्तें',
      'privacy_policy': 'गोपनीयता नीति',
      'log_out': 'लॉग आउट',
      'select_language': 'भाषा चुनें',

      // Achievement Screen
      'achievements_title': 'उपलब्धियां',
      'your_rank_stat': 'आपकी रैंक',
      'total_users_stat': 'कुल उपयोगकर्ता',
      'active_users_stat': 'सक्रिय उपयोगकर्ता',
      'active_this_month_stat': 'इस महीने सक्रिय',
      'global_ranking': 'वैश्विक रैंकिंग',
      'no_ranking_data': 'अभी तक कोई रैंकिंग डेटा नहीं है।',

      // Notifications Screen
      'notifications': 'सूचनाएं',
      'all_filter': 'सभी',
      'quizs_filter': 'क्विज़',
      'rewards_filter': 'इनाम',
      'mark_all_read': 'सभी पढ़ा हुआ करें',
      'all_notifs_read': 'सभी सूचनाएं पढ़ी गईं',
      'no_notifications': 'अभी तक कोई सूचना नहीं है।',

      // Selection Screen
      'choose_category': 'श्रेणी चुनें',
      'mathematics': 'गणित',
      'trending_quizzes': 'ट्रेंडिंग क्विज़',
      'open_trigo': 'त्रिकोणमिति क्विज़ खोलें',
      'questions_count': '10 प्रश्न',
      'played_count': 'खेले गए',

      // Question & Results
      'questions_header': 'प्रश्न',
      'q_red_planet': 'कौन सा ग्रह लाल ग्रह\nके रूप में जाना जाता है?',
      'venus': 'शुक्र',
      'mercury': 'बुध',
      'mars': 'मंगल',
      'jupiter': 'बृहस्पति',
      'previous': 'पिछला',
      'next': 'अगला',
      'mars_desc_title': 'मंगल - लाल ग्रह',
      'mars_desc_body': 'मंगल सूर्य से चौथा ग्रह है और इसकी सतह पर लोहे से भरपूर धूल के कारण इसे लाल ग्रह के रूप में जाना जाता है। इसका वातावरण पतला है, चट्टानी इलाके हैं, विशाल ज्वालामुखी, गहरी घाटियाँ और दो छोटे चंद्रमा हैं - फोबोस और डीमोस।',
      'next_trial_in_5s': 'अगला प्रयास 5s में',
      'next_trial': 'अगला प्रयास',
      'congratulations': 'बधाई हो !',
      'result_good': 'बहुत बढ़िया!',
      'result_average': 'अच्छा प्रयास!',
      'result_low': 'अभ्यास जारी रखें!',
      'result_accuracy_label': 'सटीकता: ',
      'result_time_label': '  •  समय: ',
      'question_solved': 'हल किए गए प्रश्न',
      'return_home': 'होम पर जाएं',

      // Authentication Screens (Sign Up, Sign In, Verify Code)
      'create_account': 'खाता बनाएं',
      'signup_subtitle': 'नीचे अपनी जानकारी भरें या\nअपने सोशल खाते से रजिस्टर करें',
      'name_label': 'नाम',
      'name_placeholder': 'उदा. अमन गुप्ता',
      'email_label': 'ईमेल',
      'email_placeholder': 'amangupta@gmail.com',
      'username_email_label': 'उपयोगकर्ता नाम/ईमेल',
      'password_label': 'पासवर्ड',
      'agree_terms': 'स्वीकार करें',
      'terms_condition_link': 'नियम एवं शर्तें',
      'sign_up_btn': 'साइन अप करें',
      'sign_in_btn': 'साइन इन करें',
      'or_signup_with': 'या इसके साथ साइन अप करें',
      'or_signin_with': 'या इसके साथ साइन इन करें',
      'continue_with_google': 'Google के साथ जारी रखें',
      'already_have_account': 'क्या आपके पास पहले से खाता है?',
      'dont_have_account': 'खाता नहीं है?',
      'sign_in_link': 'साइन इन करें',
      'sign_up_link': 'साइन अप करें',
      'welcome_back': 'वापसी पर स्वागत है',
      'welcome_back_subtitle': 'क्या आप अपने ज्ञान का परीक्षण करने के लिए तैयार हैं?',
      'forgot_password': 'पासवर्ड भूल गए?',
      'forgot_password_subtitle': 'अपना पंजीकृत ईमेल दर्ज करें और हम पासवर्ड रीसेट करने के लिए सत्यापन कोड भेजेंगे।',
      'send_code_btn': 'रीसेट कोड भेजें',
      'remember_password': 'क्या आपको अपना पासवर्ड याद है?',
      'verify_code': 'कोड सत्यापित करें',
      'verify_code_subtitle': 'कृपया वह कोड दर्ज करें जो हमने अभी ईमेल पर भेजा है',
      'dont_receive_otp': 'ओटीपी नहीं मिला?',
      'resend_code': 'कोड पुनः भेजें',
      'verify_btn': 'सत्यापित करें',
      'reset_password_btn': 'पासवर्ड रीसेट करें',
      // Edit Profile & Account Management
      'save_changes': 'बदलाव सहेजें',
      'change_password': 'पासवर्ड बदलें',
      'new_password': 'नया पासवर्ड',
      'confirm_password': 'पासवर्ड की पुष्टि करें',
      'delete_account': 'खाता हटाएं',
      'delete_account_confirm_title': 'खाता हटाएं',
      'delete_account_confirm_msg': 'क्या आप वाकई अपना खाता स्थायी रूप से हटाना चाहते हैं? यह क्रिया पूर्ववत नहीं की जा सकती।',
      'profile_updated_success': 'प्रोफ़ाइल सफलतापूर्वक अपडेट हो गई!',
      'password_changed_success': 'पासवर्ड सफलतापूर्वक बदल दिया गया!',
      'delete_btn': 'हटाएं',

      // Attendance Screen
      'attendance_title': 'उपस्थिति',
      'official_attendance_log': 'आधिकारिक स्कूल उपस्थिति रिकॉर्ड',
      'present': 'उपस्थित',
      'absent': 'अनुपस्थित',
      'leave': 'अवकाश',
      'not_marked': 'दर्ज नहीं',
      'todays_status': 'आज की स्थिति',
      'today': 'आज',
      'official_record': 'आधिकारिक रिकॉर्ड',
      'days_short': 'दिन',
      'overall_rate': 'कुल उपस्थिति दर',
      'great': 'उत्कृष्ट',
      'average': 'सामान्य',
      'low': 'कम',
      'sessions_attended': 'सत्रों में उपस्थित',
      'of_word': 'में से',
      'updated_by_admin': 'स्कूल प्रशासन द्वारा अद्यतन',
      'attendance_log': 'उपस्थिति रिकॉर्ड',
      'no_attendance_recorded': 'अभी तक कोई उपस्थिति दर्ज नहीं है',
      'no_attendance_sub': 'जब आपके शिक्षक उपस्थिति दर्ज करेंगे, तो वह यहां दिखाई देगी।',
      'official_attendance_entry': 'आधिकारिक उपस्थिति प्रविष्टि',
      'could_not_load_attendance': 'उपस्थिति डेटा लोड नहीं हो सका।',
      'check_internet_connection': 'कृपया अपना इंटरनेट कनेक्शन जांचें।',
      'retry': 'पुनः प्रयास करें',
    },
  };
}
