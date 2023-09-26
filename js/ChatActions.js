class ChatActions {

    static connected(data) {
      $('.user_img_profile').attr('src', data.avatar);
      $('#nick').text(data.nickName);
      ChatActions.populateCreateGroups(data.chats);
      ChatActions.populateSearch(data.search);
      ChatActions.populateChatList(data.chats);
      ChatActions.populateGroupList(data.groups);      
    }
  
    static changeName(data) {
      $('#nick').text(data.nickName);
    }
  
    static changeAvatar(data) {
      $('.user_img_profile').attr('src', data.avatar);
    }
  
    static chatAdd(data) {
      ChatActions.populateChatList(data.chats);
    }
  
    static groupAdd(data) {
      ChatActions.populateGroupList(data.groups);
    }
  
    static getBoard(data) {
    // вставка количества сообщений
		$('#msg_count').text(data.messages_count + ' сообщений');
    // очистка борда
		$('#messages-container').empty();
		// вставка ника и аватара в шапку
    if(data.name_chat != null) {
      $('#name-chat').text('Г/Ч ' + data.name_chat);
      $('#hes_avatar').attr('src', '/img/group.png');
    } else {
      $('#name-chat').text('Чат с ' + data.hes_members[0].nick);
      $('#hes_avatar').attr('src', data.hes_members[0].avatar);
    }	
		// Получаем ID текущего пользователя
		let currentUserId = window.user_id;
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
    }
  
    static populateSearch(searchArray) {
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
    }
  
    static populateChatList(chats) {
      const chatListContainer = $('#chatListContainer');
      chatListContainer.empty();
      chats.forEach(function(chat) {
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
    }
  
    static populateGroupList(groups) {
      const chatListContainer = $('#groupListContainer');
			  
      groups.forEach(function(group) {
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
    }
}

export default ChatActions;