$(document).ready(function() {
	$('body').css({
		'background-image': '',
		'background-size': '',
		'background-repeat': '',
		'background': '#7F7FD5',
		'background': '-webkit-linear-gradient(to right, #090c15, #22072a, #780c3d)',
		'background': 'linear-gradient(to right, #090c15, #22072a, #780c3d)'
	});
});

// Открытие Веб сокет соединения
const socket = new WebSocket('ws://localhost:2346');

// Получение пользователя для определения интерфейса
let user_data = $('#user_data').val();

// Отправка юзера на сервер 
socket.onopen = function() {
    console.log('Соединение открыто ' + user_data);
    const message = JSON.stringify({data: user_data, action: 'start'});	
    socket.send(message); 
}

// Обработка ответов на полученные сообщения
socket.onmessage = function(event) {
    let data = JSON.parse(event.data);
    console.log('Получены данные:', data);

	// получение id в глобальную видимость
	window.user_id = data.id;
	let action = data.action;
	let message = data.message;

	switch (action) {
		case 'connected':
			// получение текущего аватара
			$('.user_img_profile').attr('src', data.avatar);

			// получение текущего ника
			$('#nick').text(data.nickName);

			// получение массива для поиска
			$(document).ready(function() {
				let searchArray = data.search;
				let $selectElement = $('#search');
			  
				$.each(searchArray, function(index, item) {
					let userId = item.user_id;
					let text = item.result;
					let $optionElement = $('<option>')
						.val(userId)
						.text(text)
						.data('nick', userId);
					$selectElement.append($optionElement);
				});
			});

			// получение списка активных чатов
			$(document).ready(function() {
				const chatListContainer = $('#chatListContainer');
			  
				data.chats.forEach(function(chat) {
				  const listItem = $('<li>')
					.attr('id', chat.chat_id)			
					.addClass('board');					
			  
				  const dFlexContainer = $('<div>').addClass('d-flex').addClass('bd-highlight');
				  const imgContainer = $('<div>').addClass('img_cont');
				  const userImg = $('<img>').attr('src', chat.avatar).addClass('rounded-circle user_img');
				  const onlineIcon = $('<span>').addClass('online_icon offline');
				  const userInfoContainer = $('<div>').addClass('user_info');
				  const userName = $('<span>').text(chat.name);
				  const userStatus = $('<p>').text(chat.name + ' is online');
			  
				  imgContainer.append(userImg);
				  imgContainer.append(onlineIcon);
				  userInfoContainer.append(userName);
				  userInfoContainer.append(userStatus);
				  dFlexContainer.append(imgContainer);
				  dFlexContainer.append(userInfoContainer);
				  listItem.append(dFlexContainer);
				  chatListContainer.append(listItem);
				});
			});
		
			// получение списка активных групповых чатов
			$(document).ready(function() {
				const chatListContainer = $('#groupListContainer');
			  
				data.groups.forEach(function(group) {
				  const listItem = $('<li>')
					.attr('id', group.chat_id)
					.addClass('board')					
					.appendTo(chatListContainer); 
			  
				  const dFlexContainer = $('<div>').addClass('d-flex').addClass('bd-highlight');
				  const imgContainer = $('<div>').addClass('img_cont');
				  const userImg = $('<img>').attr('src', "/img/group.png").addClass('rounded-circle user_img');
				  const userInfoContainer = $('<div>').addClass('user_info');
				  const userName = $('<span>').text('Г/Ч ' + group.group_name);
			  
				  imgContainer.append(userImg);
				  userInfoContainer.append(userName);
				  dFlexContainer.append(imgContainer);
				  dFlexContainer.append(userInfoContainer);
				  listItem.append(dFlexContainer);		  
				});
			});

			// Обновляем список для создания групп
			let chats = data.chats;

			let optionsHtml = '';
			for (var i = 0; i < chats.length; i++) {
				let chat = chats[i];
				optionsHtml += '<label><input type="checkbox" name="names[]" value="' + chat.user_id + '">' + chat.name + '</label>';
			};

			// Список для чекбоксов из формы 
			$('#creategroups').html(optionsHtml);
			(function($) {
				function setChecked(target) {
					let checked = $(target).find("input[type='checkbox']:checked").length;
					if (checked) {
						$(target).find('select option:first').html('Выбрано: ' + checked);
					} else {
						$(target).find('select option:first').html('Выберите из списка');
					}
				}
			 
				$.fn.checkselect = function() {
					this.wrapInner('<div class="checkselect-popup"></div>');
					this.prepend(
						'<div class="checkselect-control">' +
							'<select class="form-control"><option></option></select>' +
							'<div class="checkselect-over"></div>' +
						'</div>'
					);	
			 
					this.each(function(){
						setChecked(this);
					});		
					this.find('input[type="checkbox"]').click(function(){
						setChecked($(this).parents('.checkselect'));
					});
			 
					this.parent().find('.checkselect-control').on('click', function(){
						$popup = $(this).next();
						$('.checkselect-popup').not($popup).css('display', 'none');
						if ($popup.is(':hidden')) {
							$popup.css('display', 'block');
							$(this).find('select').focus();
						} else {
							$popup.css('display', 'none');
						}
					});
			 
					$('html, body').on('click', function(e){
						if ($(e.target).closest('.checkselect').length == 0){
							$('.checkselect-popup').css('display', 'none');
						}
					});
				};
			})(jQuery);	 
			$('.checkselect').checkselect();
			break;

		case 'change_name':
			// получение текущего ника
			if (message){
				console.log(message);
			}
			$('#nick').text(data.nickName);
			break;

		case 'change_avatar':
			// получение текущего аватара
			if (message){
				console.log(message);
			}
			$('.user_img_profile').attr('src', data.avatar);
			break;

		case 'chat_add':
			// получение текущего списка чатов
			if (message){
				console.log(message);
			}
			$(document).ready(function() {				
				const chatListContainer = $('#chatListContainer');
				chatListContainer.empty();			  
				data.chats.forEach(function(chat) {
				  const listItem = $('<li>')
					.attr('id', chat.chat_id)			
					.addClass('board');
			  
				  const dFlexContainer = $('<div>').addClass('d-flex').addClass('bd-highlight');
				  const imgContainer = $('<div>').addClass('img_cont');
				  const userImg = $('<img>').attr('src', chat.avatar).addClass('rounded-circle user_img');
				  const onlineIcon = $('<span>').addClass('online_icon offline');
				  const userInfoContainer = $('<div>').addClass('user_info');
				  const userName = $('<span>').text(chat.name);
				  const userStatus = $('<p>').text(chat.name + ' is online');
			  
				  imgContainer.append(userImg);
				  imgContainer.append(onlineIcon);
				  userInfoContainer.append(userName);
				  userInfoContainer.append(userStatus);
				  dFlexContainer.append(imgContainer);
				  dFlexContainer.append(userInfoContainer);
				  listItem.append(dFlexContainer);
				  chatListContainer.append(listItem);
				});
			});		
			break;

		case 'group_add':
			// получение текущего списка групп чатов
			if (message){
				console.log(message);
			}
			$(document).ready(function() {
				const chatListContainer = $('#groupListContainer');
				chatListContainer.empty();
				data.groups.forEach(function(group) {
				  const listItem = $('<li>')
					.attr('id', group.chat_id)
					.addClass('board')								
					.appendTo(chatListContainer); 
			  
				  const dFlexContainer = $('<div>').addClass('d-flex').addClass('bd-highlight');
				  const imgContainer = $('<div>').addClass('img_cont');
				  const userImg = $('<img>').attr('src', "/img/group.png").addClass('rounded-circle user_img');
				  const userInfoContainer = $('<div>').addClass('user_info');
				  const userName = $('<span>').text('Г/Ч ' + group.group_name);
			  
				  imgContainer.append(userImg);
				  userInfoContainer.append(userName);
				  dFlexContainer.append(imgContainer);
				  dFlexContainer.append(userInfoContainer);
				  listItem.append(dFlexContainer);		  
				});
			});
			break;

		case 'get_board':
			if (message){
				console.log(message);
			}
			// вставка количества сообщений
			$('#msg_count').text(data.messages_count + ' сообщений');
			// вставка ника ток для чата
			$('#name-chat').text('Чат с ' + data.hes_members[0].nick);
			// вставка аватара текущего собеседника
			$('#hes_avatar').attr('src', data.hes_members[0].avatar);
			// Получаем ID текущего пользователя
			let currentUserIdChat = window.user_id;
			// очистка борда
			$('#messages-container').empty();			
			// Генерируем сообщения
			data.messages.forEach(message => {
				// Определяем, является ли отправитель текущим пользователем
				let isCurrentUser = message.sender_id === currentUserIdChat;			
				// Определяем класс для выравнивания сообщения
				let justifyClass = isCurrentUser ? 'justify-content-end' : 'justify-content-start';				
				// Определяем класс для аватара
				let avatarClass = isCurrentUser ? data.avatar : data.hes_members[0].avatar;				
				// Форматируем время сообщения
				let messageTime = moment(message.timestamp).format('h:mm A, MMMM Do');			
				// Генерируем HTML для сообщения
				const messageHtml = `
					<div class="d-flex ${justifyClass} mb-4" data-msg-id="${message.message_id}" >
						<div class="img_cont_msg">
						<img src="${avatarClass}" class="rounded-circle user_img_msg">
						</div>
						<div class="msg_cotainer${isCurrentUser ? '_send' : ''}">
						${message.message_text}
						<span class="msg_time">${messageTime}</span>					
						</div>
					</div>
				`;				
			  // Вставляем сообщение в DOM
			  $('#messages-container').append(messageHtml);
			});
			break;

		case 'get_board_group':

		if (message){
			console.log(message);
		}
		
		// вставка количества сообщений
		$('#msg_count').text(data.messages_count + ' сообщений');
		// вставка ника ток для груп чата
		$('#name-chat').text('Г/Ч ' + data.name_chat);		
		// вставка аватара текущего собеседника он же дефолтный
		$('#hes_avatar').attr('src', '/img/group.png');
		// Получаем ID текущего пользователя
		let currentUserId = window.user_id;		
		// очистка борда
		$('#messages-container').empty();		
		// Генерируем сообщения
		data.messages.forEach(message => {
			// Определяем, является ли отправитель текущим пользователем
			let isCurrentUser = message.sender_id === currentUserId;			
			// Определяем класс для выравнивания сообщения
			let justifyClass = isCurrentUser ? 'justify-content-end' : 'justify-content-start';				
			// Определяем класс для аватара			
			let sender = data.hes_members.find(member => member.id === message.sender_id.toString());
			let avatarClassUser = sender ? sender.avatar : data.avatar;			
			// Форматируем время сообщения
			let messageTime = moment(message.timestamp).format('h:mm A, MMMM Do');		
			// Генерируем HTML для сообщения
			const messageHtml = `
				<div class="d-flex ${justifyClass} mb-4" data-msg-id="${message.message_id}" >
					<div class="img_cont_msg">
					<img src="${avatarClassUser}" class="rounded-circle user_img_msg">
					</div>
					<div class="msg_cotainer${isCurrentUser ? '_send' : ''}">
					${message.message_text}
					<span class="msg_time">${messageTime}</span>					
					</div>
				</div>
			`;				
		  // Вставляем сообщение в DOM
		  $('#messages-container').append(messageHtml);
		});
	break;
	}
};

// Обработчик события закрытия соединения
socket.onclose = function() {
    console.log('Соединение закрыто');
};

// Обработчик события ошибки соединения
socket.onerror = function(error) {
    console.error('Произошла ошибка:', error);
};

// отправка создания чата и строка поиска
$(document).ready(function() {

	let selectedUserId;
  
	$('.js-select2').select2({
	  placeholder: "Поиск пользователей",
	  maximumSelectionLength: 2,
	  language: "ru"
	});

	$('.js-select2').on('select2:select', function(e) {
		let $selectedOption = $(e.params.data.element);
		selectedUserId = $selectedOption.data('nick');
		console.log("Пользователь " + selectedUserId);
	});

	$('#add_btn').on('click', function() {	  
	  if (selectedUserId) {
		console.log("Пользователь " + selectedUserId);
		let usersData = {
			userSender: 	window.user_id,
			userRecieved:	selectedUserId,
		};
		let data = {
			action:	'chat_add',
			data:	usersData,
		};

		let jsonData = JSON.stringify(data);
		socket.send(jsonData);
		console.log("данные отправляемые на сервер: ", jsonData);		
	  } else {
		console.log("Пользователь не выбран.");
	  }
	});
});

// отправка на смену ника
$(document).ready(function() {
	$('#nickChange').on('click', function(event) {
        event.preventDefault();
	  	
	  	let nickValue = $('#newNick').val();
		let isEmailHidden = $('#hideEmail').is(':checked');
		let hideEmail = isEmailHidden ? 1 : 0;

		let formData = {
			userId:		window.user_id,
			nickName:	nickValue,
			hideEmail:	hideEmail,
		};

	  	let data = {
			action:		'change_nick',
			data:		formData,
	  	};
	  
	  	let jsonData = JSON.stringify(data);
	  	//console.log(jsonData);

	  	socket.send(jsonData);
		$('#newNick').val('');
		$('.setting__popup').removeClass('active');	  	  
	});
});

// отправка на смену аватара
$('#profile_change').submit(function(event) {
    event.preventDefault();

    let fileInput = document.querySelector('input[type="file"]');
    let file = fileInput.files[0];

    let reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = function() {
        let fileData = {
            userId: window.user_id,
            imageData: {
                name: file.name,
                type: file.type,
                size: file.size,
                data: reader.result
            }
        };

        let data = {
            action: 'change_avatar',
            data: fileData
        };

        let jsonData = JSON.stringify(data);
        socket.send(jsonData);
        //console.log(jsonData);
    };
});

// отправка на создание груп чата
$(document).ready(function() {
	$('#group_create').submit(function(event) {
		event.preventDefault();
	
		let formData2 = $(this).serializeArray();
		let groupname = $('input[name="groupname"]').val();
		let selectedValues = [];
  
		$('input[name="names[]"]:checked').each(function() { 
			selectedValues.push($(this).val());
		});

		selectedValues = selectedValues.map(function(val) {
			return parseInt(val);
		});

		selectedValues.push(window.user_id);		
		
		let formData = {
		 	userId: window.user_id,
			groupData: {
				nameGroup: groupname,
				members: selectedValues	
			}
		};

		let data = {
			action: 'group_add',
			data: formData
		};

		let jsonData = JSON.stringify(data);
		socket.send(jsonData);

		console.log(jsonData);
		$('.create__popup').removeClass('active');
	});
});

// отправка на получение борда с сообщениями
$('.contacts').on('click', '.board', function() {
	let chatId = $(this).attr('id');
	$('#name-chat').attr('data-chat', chatId);
	let formData = {
		userId: window.user_id,
		chatId: chatId
	};
	let data = {
		action: 'get_board',
		data: formData
	};
	let jsonData = JSON.stringify(data);
	socket.send(jsonData);
	console.log(jsonData);
});

// отправка сообщений
$(document).ready(function() {
	$('#send-message').click(function() {
		
		let messageText = $('#message').val();
		let chatId = $('#name-chat').attr('data-chat');

		let formData = {
			userId:		window.user_id,
			chatId:	    chatId,
			msgText:	messageText			
		};

	  	let data = {
			action:		'send_message',
			data:		formData
	  	};
	  
	  	let jsonData = JSON.stringify(data);
	  	console.log(jsonData);

	  	socket.send(jsonData);
		$('#message').val('');		
	});
});

// выпадающее меню справа сверху
$(document).ready(function(){
    $('#action_menu_btn').click(function(){
        $('.action_menu').toggle();
    });
});

// модальные окна
$('#profile').on('click', function() {
  $('.profile__popup').addClass('active');
});

$('#setting').on('click', function() {
  $('.setting__popup').addClass('active');
});

$('#create').on('click', function() {
  $('.create__popup').addClass('active');
});

$('#exit').on('click', function() {
  window.location.href = '/Chat/logout';
});

// крестик для закрытия модальных окон
$('.close_popup').on('click', function() {
  $('.profile__popup').removeClass('active');  
  $('.setting__popup').removeClass('active');  
  $('.create__popup').removeClass('active');
});