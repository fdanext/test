
document.addEventListener("DOMContentLoaded", function() {
	let search_output = document.getElementById("search_output");
	
	let search_input = document.getElementsByName("query")[0];
	search_input.addEventListener("click", ReleaseError);

	let search_button = document.getElementById("search");
	search_button.addEventListener("click", function() {
		SendQuery(search_input.value);
	});
});

function SendQuery(search_value) {
	if(search_value.length >= MIN_LENGTH) {
		let xhr = new XMLHttpRequest();
		xhr.open("GET", "out.php?query=" + search_value, true);

		xhr.send();

		xhr.onreadystatechange = function() {
			window.history.pushState("", "", "?query=" + search_value);
			
			let search_output = document.getElementById("search_output");
			search_output.innerHTML = '';
			
			if (xhr.readyState != 4) {
				ShowMessage("Загрузка..");
				return;
			}

			if (xhr.status != 200) {
				let message = "Ошибка! " + xhr.status + ": " + xhr.statusText;
				ShowMessage(message);
				return;
			} else {
				HandleResponse(search_value, xhr.responseText);
			}
		}
	} else {
		let search_input = document.getElementsByName("query")[0];
		search_input.className = "error";
	}
}

function HandleResponse(search_value, resp) {
	let res_json;
	try {
		res_json = JSON.parse(resp);
	} catch(e) {
		let message = "Ошибка " + e.name + ":" + e.message + "\n" + e.stack;
		ShowMessage(message);
		return;
	}
	
	if (res_json.length == 0) {
		ShowMessage("Ничего не найдено");
		return;
	}
	
	HideMessage();
	
	let search_output = document.getElementById("search_output");
	let template_elm = document.getElementsByClassName("blog")[0];
	
	res_json.forEach(function(post) {
		let clone_post = template_elm.cloneNode(true);
		clone_post.getElementsByClassName("title")[0].textContent = post['title'];
		let comments_elm = clone_post.getElementsByClassName("comment")[0];
		
		post['comments'].forEach(function(comment) {
			let clone_comments = comments_elm.cloneNode(true);
			
			let comment_user = clone_comments.getElementsByClassName("comment_user")[0].firstChild;
			comment_user.nodeValue = comment['name'];
			
			let comment_user_email = clone_comments.getElementsByTagName("a")[0];
			comment_user_email.href = 'mailto:' + comment['email'];
			comment_user_email.textContent = comment['email'];
			
			let comment_body_hl = comment['body'].replace(new RegExp(search_value,'g'),"<span>" + search_value + "</span>");
			clone_comments.getElementsByClassName("comment_body")[0].innerHTML = comment_body_hl;
			
			clone_post.appendChild(clone_comments);
		});
		
		comments_elm.remove();
		clone_post.className = "blog";
		search_output.appendChild(clone_post);
	});
}

function ReleaseError() {
	let search_input = document.getElementsByName("query")[0];
	if(search_input.className == "error") {
		search_input.className = "valid";
	}
}

function ShowMessage(message_value) {
	let message_elm = document.getElementById("message");
	message_elm.textContent = message_value;
	message_elm.className = "message";
}

function HideMessage() {
	let message_elm = document.getElementById("message");
	message_elm.className = "hidden";
}