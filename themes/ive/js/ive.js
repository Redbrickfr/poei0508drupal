(function($, Drupal, drupalSettings) {
$(document).ready(function(){
	alert('uid:' + drupalSettings.user.uid);
	alert('langue:' + drupalSettings.path.currentLanguage);
});
})(jQuery, Drupal, drupalSettings);