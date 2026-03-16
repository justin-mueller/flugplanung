const CHATBOX_INITIAL_LIMIT = 20;
const CHATBOX_LAZY_LIMIT = 5;

let chatboxOffset = 0;
let chatboxHasMore = true;
let chatboxLoading = false;
let chatboxNewestDatetime = null;

function initializeChatboxLazyLoading() {
	$('#chatbox-messages').off('scroll.chatbox').on('scroll.chatbox', function () {
		if (chatboxLoading || !chatboxHasMore) {
			return;
		}

		if ($(this).scrollTop() <= 0) {
			loadChatbox(false, false, CHATBOX_LAZY_LIMIT);
		}
	});
}

function renderChatboxMessage(entry) {
	const myMessage = entry.pilot_id == User_Information.pilot_id;
	let avatar = `a${entry.avatar}.png`;

	if (myMessage) {
		return `
		<div class="d-flex flex-row justify-content-end mb-4">
			<div class="p-2 me-2 border chatbox-message-me">
				<p class="small mb-0" style="font-style: italic; font-size: 12px;">${entry.datetime}</p>
				<p class="small mb-1" style="font-weight: 700;">${entry.firstname} ${entry.lastname}</p>
				<p class="small mb-0">${entry.text}</p>
			</div>
			<img src="img/${avatar}" alt="user avatar" class="chatbox-avatar">
		</div>
		`;
	}

	return `
	<div class="d-flex flex-row justify-content-start mb-4">
		<img src="img/${avatar}" alt="user avatar" class="chatbox-avatar">
		<div class="p-2 me-2 border chatbox-message-other-user">
			<p class="small mb-0" style="font-style: italic; font-size: 12px;">${entry.datetime}</p>
			<p class="small mb-1" style="font-weight: 700;">${entry.firstname} ${entry.lastname}</p>
			<p class="small mb-0">${entry.text}</p>
		</div>
	</div>
	`;
}

function loadChatbox(scroll, reset = true, limit = null) {
	if (chatboxLoading) {
		return;
	}

	initializeChatboxLazyLoading();

	const $chatboxMessages = $('#chatbox-messages');
	const isOlderLoad = reset === false;
	const isIncrementalRefresh = reset === true && !scroll && chatboxOffset > 0 && chatboxNewestDatetime !== null;

	chatboxLoading = true;

	if (!isIncrementalRefresh) {
		toggleSpinner(true, '#chatbox-spinner');
	}

	const effectiveLimit = limit ?? (isOlderLoad ? CHATBOX_LAZY_LIMIT : CHATBOX_INITIAL_LIMIT);
	const currentOffset = reset ? 0 : chatboxOffset;
	const previousHeight = $chatboxMessages[0].scrollHeight;
	const previousScrollTop = $chatboxMessages.scrollTop();
	const previousDistanceFromBottom = previousHeight - previousScrollTop;
	const requestData = isIncrementalRefresh
		? { newerThan: chatboxNewestDatetime, limit: 50 }
		: { offset: currentOffset, limit: effectiveLimit };

	$.ajax({
		url: 'loadChatbox.php',
		type: 'GET',
		dataType: 'json',
		data: requestData,
		success: function (data) {
			const messages = Array.isArray(data.messages) ? data.messages : [];

			if (reset && !isIncrementalRefresh) {
				$chatboxMessages.empty();
				chatboxOffset = 0;
				chatboxNewestDatetime = null;
			}

			if (isOlderLoad) {
				messages.reverse();
			}

			$.each(messages, function (index, entry) {
				const msg = renderChatboxMessage(entry);
				if (isOlderLoad) {
					$chatboxMessages.prepend(msg);
				} else {
					$chatboxMessages.append(msg);
				}
			});

			chatboxOffset += messages.length;
			if (!isIncrementalRefresh) {
				chatboxHasMore = data.hasMore === true;
			}

			if (messages.length > 0 && !isOlderLoad) {
				chatboxNewestDatetime = messages[messages.length - 1].datetime;
			}

			if (reset && scroll) {
				$chatboxMessages.scrollTop($chatboxMessages[0].scrollHeight);
			} else if (isOlderLoad && messages.length > 0) {
				const newHeight = $chatboxMessages[0].scrollHeight;
				$chatboxMessages.scrollTop(newHeight - previousHeight);
			} else if (reset && !isIncrementalRefresh) {
				const newHeight = $chatboxMessages[0].scrollHeight;
				$chatboxMessages.scrollTop(Math.max(0, newHeight - previousDistanceFromBottom));
			} else if (isIncrementalRefresh) {
				const newHeight = $chatboxMessages[0].scrollHeight;
				$chatboxMessages.scrollTop(Math.max(0, newHeight - previousDistanceFromBottom));
			}
		},
		error: function (error) {
			console.error('Chatbox konnte nicht geladen werden!', error);
		},
		complete: function () {
			chatboxLoading = false;
			if (!isIncrementalRefresh) {
				toggleSpinner(false, '#chatbox-spinner');
			}
		}
	});
}

function enterChatboxMessage(scroll) {
	let text = $('#chatboxMessage').val()

	if (text == 'einhorn') { // easter egg :)
		$('#unicorn').addClass('move-image');
	} else if (text != '') {
		$.ajax({
			url: 'enterChatbox.php',
			type: 'POST',
			data: { text: text, pilot_id: User_Information.pilot_id },
			success: function () {
				loadChatbox(scroll, true, CHATBOX_INITIAL_LIMIT);
				$('#chatboxMessage').val('')
			},
			error: function (error) {
				showToast('Oops!', 'Etwas ist schiefgegangen!', 'Deine Nachricht konnte nicht gespeichtert werden!', 'error');
				console.error('Error inserting Chatbox Message:', error.responseText);
			}
		});
	}
} 