// сдвоенная форма авторизации и регистрации

$(document).ready(function() {
	$('body').css({
        'background-image': 'url("http://getwallpapers.com/wallpaper/full/a/5/d/544750.jpg")',
        'background-size': 'cover',
        'background-repeat': 'no-repeat'
	});
});

$('#showRegistr').click(function(event) {
    event.preventDefault();
    $('#registrForm').removeAttr('hidden');
    $('#loginForm').attr('hidden', true);
    $('#showRegistr').removeClass().addClass('btn btn-primary btn-lg active reg_btn');
    $('#showLogin').removeClass().addClass('btn btn-secondary btn-lg active log_btn');   
});

$('#showLogin').click(function(event) {
    event.preventDefault();
    $('#loginForm').removeAttr('hidden');
    $('#registrForm').attr('hidden', true);
    $('#showRegistr').removeClass().addClass('btn btn-secondary btn-lg active reg_btn');
    $('#showLogin').removeClass().addClass('btn btn-primary btn-lg active log_btn');    
});