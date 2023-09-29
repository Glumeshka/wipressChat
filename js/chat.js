class ChatActions {

    static connected(data) {
	  ChatActions.updateUser(data);	  
      ChatActions.populateSearch(data.search);
      ChatActions.populateChatList(data.chats);
      ChatActions.populateGroupList(data.groups);
	  ChatActions.populateCreateGroups(data.chats);
    }
  
    static updateUser(data) {
      $('#nick').text(data.nickName);
	  $('.user_img_profile').attr('src', data.avatar);
    }
  
    static getBoard(data) {
	  $('#name-chat').attr('data-chat', data.chat_id);
	  $('.card-footer').addClass('active');
	  // вставка количества сообщений
	  $('#msg_count').text(data.messages_count + ' сообщений');
	  // очистка борда
	  $('#messages-container').empty();
	  // вставка ника и аватара в шапку
	  if(data.name_chat != false) {
    	$('#name-chat').text('Г/Ч ' + data.name_chat);
      	$('#hes_avatar').attr('src', '/img/group.png');
      } else {
        $('#name-chat').text('Чат с ' + data.hes_members[0].nick);
        $('#hes_avatar').attr('src', data.hes_members[0].avatar);
      }	
	  // Получаем ID текущего пользователя
	  let currentUserId = window.user_id;
	  // Получаем Значение тихого режима
	  if(data.mute_chat == 0) {
		$('#mute-chat').removeClass().addClass('fas fa-bell');		
	  } else if (data.mute_chat == 1) {
		$('#mute-chat').removeClass().addClass('fas fa-bell-slash');		
	  }

	  // Генерируем сообщения
	  data.messages.forEach(message => {
		// Определяем, является ли отправитель текущим пользователем
		let isCurrentUser = message.sender_id === currentUserId;			
		// Определяем класс для выравнивания сообщения
		let justifyClass = isCurrentUser ? 'justify-content-end' : 'justify-content-start';				
		// Определяем аватар			
		let sender = data.hes_members.find(member => member.id === message.sender_id.toString());
		let avatarClassUser = sender ? sender.avatar : data.avatar;			
		// Форматируем время сообщения
		let messageTime = moment(message.timestamp).format('h:mm A, MM Do');		
		// Генерируем HTML для сообщения
		let messageHtml = `
			<div class="d-flex ${justifyClass} mb-4" data-msg-id="${message.message_id}" >
				<div class="img_cont_msg">
					<img src="${avatarClassUser}" class="rounded-circle user_img_msg">
				</div>
				<div class="msg_container${isCurrentUser ? '_send' : ''}">
				<span class="msg_text${isCurrentUser ? '_send' : ''}">${message.message_text}</span>
				<span class="msg_time">${messageTime}</span>`;
				// Добавляем класс "has-right-click-menu" для элемента, который будет содержать меню
				if (justifyClass === 'justify-content-end') {
					messageHtml += `	
					<ul class="right-click-menu">
						<li class="resend">Переслать</li>
						<li class="edit">Изменить</li>
						<li class="delete">Удалить</li>							
					</ul>`;
				}
				messageHtml += `
					</div>
				</div>
				`;
			// Вставляем сообщение в DOM
		const $message = $(messageHtml);
		$('#messages-container').append($message);
	  });
    }
  
    static populateSearch(search) {
      let $selectElement = $('#search');			  
      $.each(search, function(index, item) {
        let userId = item.user_id;
        let text = item.result;
        let $optionElement = $('<option>')
          .val(userId)
          .text(text)
          .data('nick', userId);
        $selectElement.append($optionElement);
      });
    }
  
    static populateChatList(chats) {
      const chatListContainer = $('#chatListContainer');
      chatListContainer.empty();

      chats.forEach(function(chat) {

        const listItem = $('<li>')
        .attr('id', chat.chat_id)			
        .addClass('board');
		
		if (chat.mute === 1) {
		  listItem.addClass('mute');
		} else if (chat.mute === 0) {
		  listItem.removeClass('mute');
		}	
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
    }
  
    static populateGroupList(groups) {
      const chatListContainer = $('#groupListContainer');
	  chatListContainer.empty();

      groups.forEach(function(group) {

        const listItem = $('<li>')
        .attr('id', group.chat_id)
        .addClass('board');

		if (group.mute === 1) {
		  listItem.addClass('mute');
		} else if (group.mute === 0) {
		  listItem.removeClass('mute');
		}	

		listItem.appendTo(chatListContainer); 
      
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
    }
      
	static populateCreateGroups(chats) {		

	  let optionsHtml = '';
		
	 	for (var i = 0; i < chats.length; i++) {
			let chat = chats[i];
			optionsHtml += '<label><input type="checkbox" name="names[]" value="' + chat.user_id + '">' + chat.name + '</label>';
		};
		
		// Список для чекбоксов из формы 
	  $('#creategroups').html(optionsHtml);
		
	  (function($) {
		$.fn.checkselect = function() {
		  function setChecked(target) {

		    let checked = $(target).find("input[type='checkbox']:checked").length;
			if (checked) {
			  $(target).find('select option:first').html('Выбрано: ' + checked);
			} else {
			  $(target).find('select option:first').html('Выберите из списка');
			}
			  
			let $popup = $(target).next();
			if ($popup.is(':hidden')) {
			  $popup.css('display', 'block');
			  $(target).find('select').focus();
			} else {
			  $popup.css('display', 'block');
			}
		  }
		
		  this.wrapInner('<div class="checkselect-popup"></div>');
		  this.prepend(
			'<div class="checkselect-control">' +
			'<select class="form-control"><option></option></select>' +
			'<div class="checkselect-over"></div>' +
			'</div>'
		  );
		
		  this.each(function() {
			setChecked(this);
		  });

		  this.find('input[type="checkbox"]').click(function() {
			setChecked($(this).parents('.checkselect'));
		  });
		
		  this.find('.checkselect-control').on('click', function() {
			let $popup = $(this).next();
			$('.checkselect-popup').not($popup).css('display', 'none');
			  if ($popup.is(':hidden')) {
				$popup.css('display', 'block');
				$(this).find('select').focus();
			  } else {
				$popup.css('display', 'none');
			  }
		  });
		
		  $('html, body').on('click', function(e) {
			if ($(e.target).closest('.checkselect').length == 0) {
			  $('.checkselect-popup').css('display', 'none');
			}
		  });
		};
	  })(jQuery);
	  $('.checkselect').checkselect();
    }
}

// смена фона на более мягкий
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

// Делаем звук
let audio = new Audio();

// Устанавливаем путь к файлу
audio.src = '/audio/nani.mp3';

// Получение пользователя для определения интерфейса
let user_data = $('#user_data').val();

// Делаем переменную для опознавания редактированного сообщения
let edit = false;

let menuData = [];

// Отправка юзера на сервер 
socket.onopen = function() {
  console.log('Соединение открыто ' + user_data);
  const message = JSON.stringify({data: user_data, action: 'start'});	
  socket.send(message); 
};

// Обработка ответов на полученные сообщения
socket.onmessage = function(event) {
  let data = JSON.parse(event.data);
  console.log('Получены данные:', data);

  // получение id в глобальную видимость
  window.user_id = data.id;
  let action = data.action;
  let message = data.message;
  menuData = data.chats;

	switch (action) {
      case 'connected':
        if (message) {
          console.log(message);
		}
        ChatActions.connected(data);
        break;

      case 'change_name':
        if (message) {
          console.log(message);
        }
        ChatActions.updateUser(data);
        break;

      case 'change_avatar':
        if (message) {
          console.log(message);
    	}
        ChatActions.updateUser(data);
        break;

      case 'chat_add':
        if (message) {
          console.log(message);
        }
        ChatActions.populateChatList(data.chats);
		ChatActions.populateCreateGroups(data.chats);
        break;

      case 'group_add':
        if (message) {
          console.log(message);
        }
        ChatActions.populateGroupList(data.groups);
        break;

      case 'get_board':
        if (message) {
          console.log(message);
        }
		ChatActions.populateChatList(data.chats);
		ChatActions.populateGroupList(data.groups);
        ChatActions.getBoard(data);
        break;

      case 'updated':
		ChatActions.populateSearch(data.search);
		ChatActions.populateChatList(data.chats);
		ChatActions.populateGroupList(data.groups);
		ChatActions.populateCreateGroups(data.chats);		
		break;

	  case 'update_all':
		// отсекаем обновление борда если он не выбран
		let currentСhatId = $('#name-chat').attr('data-chat');
		let himChatId = data.chat_id;
		if (himChatId == currentСhatId){
		  ChatActions.populateChatList(data.chats);
		  ChatActions.populateGroupList(data.groups);
	      ChatActions.getBoard(data);
		}			
		break;

	  case 'update_chat':
		// отсылаем звук если пришло сообщение из чата который не замутан
		if(data.mute_chat == 0) {
	      audio.play();
		}
		// отсекаем обновление борда если он не выбран
		let currentСhatId2 = $('#name-chat').attr('data-chat');
		let himChatId2 = data.chat_id;
		if (himChatId2 == currentСhatId2){
		  ChatActions.populateChatList(data.chats);
		  ChatActions.populateGroupList(data.groups);
		  ChatActions.getBoard(data);
		}			
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
	    userRecieved:	selectedUserId
	  };

	  let data = {
	    action:	'chat_add',
	    data:	usersData
	  };

	  let jsonData = JSON.stringify(data);
	  socket.send(jsonData);
	  //console.log("данные отправляемые на сервер: ", jsonData);		
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
	  nickName:		nickValue,
	  hideEmail:	hideEmail
	};

	let data = {
	  action:		'change_nick',
	  data:			formData
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
	    nameGroup:	groupname,
		members:	selectedValues	
	  }
	};

	let data = {
      action: 'group_add',
	  data: formData
	};

	let jsonData = JSON.stringify(data);
	socket.send(jsonData);
	//console.log(jsonData);
	$('.create__popup').removeClass('active');		
  });
});

// отправка на получение борда с сообщениями
$('.contacts').on('click', '.board', function() {
  let chatId = $(this).attr('id');  
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
  //console.log(jsonData);
});

// отправка сообщений
$(document).ready(function() {
  $('#send-message').click(function() {
		
  let messageText = $('#message').val();
  let chatId = $('#name-chat').attr('data-chat');

  let formData = {
	userId:		window.user_id,
	chatId:	    chatId,
	edit:		edit,
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
  edit = false;		
  });
});

// отправка на смену бесшумного режима
$(document).ready(function() {
  $('#mute-chat').click(function() {
	let chatId = $('#name-chat').attr('data-chat');		
	let muteChatClass = $('#mute-chat').attr('class');
	let result;

	if (muteChatClass === 'fas fa-bell') {
      result = 0;
	} else if (muteChatClass === 'fas fa-bell-slash') {
	  result = 1;
	} 		

	let formData = {
	  userId:	window.user_id,
	  chatId:   chatId,
	  mute:		result			
	};

	let data = {
	  action:	'swap_mute',
	  data:		formData
	};
	  
	let jsonData = JSON.stringify(data);
	//console.log(jsonData);		
	socket.send(jsonData);		
  });
});

// модальное меню с 3 кнопками
document.querySelector("#messages-container").addEventListener("contextmenu", function(event) {
  event.preventDefault();
  
  let clickedElement = event.target;

  if (clickedElement.closest(".d-flex.justify-content-start")) {    
    return;
  }

  let message = clickedElement.closest(".d-flex[data-msg-id]");
  
  if (message) {
	let messageId = message.getAttribute("data-msg-id");
	let messageText = message.querySelector(".msg_text_send").textContent;

	// Отобразить ваше собственное контекстное меню
	let menu = document.querySelector(".right-click-menu");
	menu.style.left = event.pageX + "px";
	menu.style.top = event.pageY + "px";
	menu.classList.add("active");

	// кнопка переслать 
	document.querySelector(".right-click-menu .resend").addEventListener("click", function() {
	  menu.classList.remove("active");
	  let additionalMenu = document.createElement("ul");
	  additionalMenu.classList.add("additional-menu");	
	  // Перебор данных для создания элементов меню
	  menuData.forEach(function(data) {
		let menuItem = document.createElement("li");
		menuItem.textContent = data.name;

		menuItem.addEventListener("click", function() {
		  
			let formData = {
			  userId:	window.user_id,
			  chatId:	data.chat_id,
			  edit:		edit,
			  msgText:	messageText			
			};
		  
			let sendData = {
			  action:	'send_message',
			  data:		formData
			};
				
			let jsonData = JSON.stringify(sendData);
			console.log(jsonData);		
			socket.send(jsonData);

		    additionalMenu.classList.add("inactive");
	       
			console.log("Выбран пункт меню:", data.name);
			console.log("ID выбранного пункта:", data.chat_id);
			console.log("ID выбранного пользователя:", data.user_id);		  
		});
		additionalMenu.appendChild(menuItem);			
	  });
	  document.querySelector(".justify-content-end").appendChild(additionalMenu);
	});
	// кнопка изменить
	document.querySelector(".right-click-menu .edit").addEventListener("click", function() {
	  menu.classList.remove("active");
	  $('#message').val(messageText);
	  edit = messageId;
	});
	// кнопка удалить
	document.querySelector(".right-click-menu .delete").addEventListener("click", function() {
	  menu.classList.remove("active");
	  let chatId = $('#name-chat').attr('data-chat');
	  let formData = {
		userId:	window.user_id,
		chatId:	chatId,
		msgId:	messageId
	  };
		
	  let data = {
		action:	'delete_message',
		data:		formData
	  };
	
	  let jsonData = JSON.stringify(data);
	  // console.log(jsonData);		
	  socket.send(jsonData);		
	});
  }
});

document.addEventListener("click", function(event) {
  let menu = document.querySelector(".right-click-menu");
  if (menu && !menu.contains(event.target)) {
    menu.classList.remove("active");
  }
});