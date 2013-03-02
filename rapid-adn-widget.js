if(typeof(RapidADN)=='undefined'){RapidADN={};}

RapidADN.script = function(RapidADN, window, document) {
	var apis = RapidADN.apis,
		i;
	
	function callback(api, response) {
		if ( typeof response.meta.error_message != 'undefined' ) {
			return;
		}
		
		var widgets = api.widgets,
			widgets_len = widgets.length,
			the_html = '',
			include_reposts = ( api.include_rts == 'f' ) ? false : true,
			posts = response.data;

		the_html = generate_html(api.screen_name, posts, include_reposts);
		
		if ( the_html != '' ) {
			for (var i=0; i<widgets_len; i++) {
				var element = widgets[i],
					ul = document.createElement('ul');
				element = document.getElementById(element).parentNode;
			
				ul.className = 'adnposts';
				ul.innerHTML = the_html;
				element.appendChild(ul);

				removeClass(element, 'widget_adn--hidden');
			}
		}
	}
	RapidADN.callback = callback;

	function generate_html(screen_name, posts, include_reposts){
		var the_html = '';
		if ( typeof RapidADN.generate_html == 'function' ) {
			return RapidADN.generate_html(screen_name, posts);
		}
		for (var i=0, l=posts.length; i<l; i++) {
			var use_post = posts[i], 
				rt_html = '',
				post_html = '',
				classes = ['adnpost'];

			if (typeof use_post.user.username == 'undefined') {
				use_post.user.username = screen_name;
			}

			if (typeof use_post.repost_of != 'undefined') {
				if ( include_reposts == false ) {
					continue;
				}
				use_post = use_post.repost_of;
				classes.push('adnpost--repost');

				if (typeof use_post.user.username == 'undefined') {
					var mentions = posts[i].entities.mentions,
						mentions_length = mentions.length,
						mention_position = 500; //any number over 256 works
					for (var j=0; j<mentions_length; j++) {
						if (mentions[j].pos < mention_position) {
							mention_position = mentions[j].pos;
							use_post.user.username = mentions[j].name;
						}
					}
				}

				
				rt_html += '>> ';
				rt_html += '<a href="';
				rt_html += 'https://alpha.app.net/';
				rt_html += use_post.user.username;
				rt_html += '" class="adnpost__mention adnpost__mention--repost">';
				rt_html += '<span>@</span>';
				rt_html += use_post.user.username;
				rt_html += '</a>';
				rt_html += ': ';
			}
			
			if (use_post.reply_to != null) {
				classes.push('adnpost--reply');
			}

			post_html = process_entities(use_post);
			post_html = post_html.replace(/\n/g, '<br>');

			the_html += '<li class="';
			the_html += classes.join(' ');
			the_html += '">';
			the_html += rt_html;
			the_html += post_html;
			// the_html += use_post.text;
			
			
			the_html += ' ';
			the_html += '<a class="adnpost__datestamp" href="';
			the_html += use_post.canonical_url;
			the_html += '">';
			the_html += relative_time(use_post.created_at);
			the_html += '</a>';
			the_html += '</li>';
		}
		return the_html;
	}


	function relative_time(time_value) {
		var split_timevalue = time_value.split("T"),
			split_date = split_timevalue[0].split("-"),
			split_time = split_timevalue[1].replace('Z', '').split(":"),
			the_date = new Date(split_date[0], split_date[1]-1,split_date[2],split_time[0],split_time[1],split_time[2]),
			now = new Date(),
			offset = now.getTimezoneOffset() * 60 * 1000,
			delta = (now.getTime() + offset - the_date.getTime()) / 1000,
			monthNames = [ "Jan", "Feb", "Mar", "Apr", "May", "Jun",
				"Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ];

		if(delta < 60) {
			return 'less than a minute ago';
		}
		else if(delta < 120) {
			return 'about a minute ago';
		}
		else if(delta < (45*60)) {
			return (parseInt(delta / 60)).toString() + ' minutes ago';
		}
		else if(delta < (90*60)) {
			return 'about an hour ago';
		}
		else if(delta < (24*60*60)) {
			return 'about ' + (parseInt(delta / 3600)).toString() + ' hours ago';
		}
		else if(delta < (48*60*60)) {
			return '1 day ago';
		}
		else {
			return the_date.getDate() + ' ' + monthNames[the_date.getMonth()];
			// return (parseInt(delta / 86400)).toString() + ' days ago';
		}
	}
	RapidADN.relative_time = relative_time;
	
	// source: https://gist.github.com/1292496
	// Takeru Suzuki
	function process_entities (post) {
		var result = [],
			entities = [],
			lastIndex = 0,
			key,
			i,
			len,
			elem;

		for (key in post.entities) {
			for (i = 0, len = post.entities[key].length; i < len; i++) {
				elem = post.entities[key][i];
				entities[elem.pos] = {
					end: elem.pos + elem.len,
					text: function () {
						switch (key) {
							/*
							case 'media':
								return '<a href="' + elem.url + '" class="adnpost__media" title="' + elem.expanded_url + '">' + elem.display_url + '</a>';
								break;
							*/
							case 'links':
								return (elem.text)? '<a href="' + elem.url + '" class="adnpost__link">' + elem.text + '</a>': elem.text;
								break;
							case 'mentions':
								var reply_class = (elem.pos == 0) ? ' adnpost__mention--reply' : '';
								return '<a href="https://alpha.app.net/' + elem.name + '" class="adnpost__mention'+reply_class+'"><span>@</span>' + elem.name + '</a>';
								break;
							case 'hashtags':
								return '<a href="https://alpha.app.net/hashtags/' + elem.name + '" class="adnpost__hashtag"><span>#</span>' + elem.name + '</a>';
								break;
							default:
								return '';
						}
					}()
				};
			}
		}
		
		for (i = 0, len = entities.length; i < len; i++) {
			if (entities[i]) {
				elem = entities[i];
				result.push(post.text.substring(lastIndex, i));
				result.push(elem.text);
				lastIndex = elem.end;
				i = elem.end - 1;
			}
		}
		
		result.push(post.text.substring(lastIndex));
		return result.join('');
	}	
	RapidADN.process_entities = process_entities;

	function removeClass(element, class_name) {
		var regexp = new RegExp('(\\s|^)'+class_name+'(\\s|$)');
		element.className = element.className.replace(regexp, ' ');
	}


	for (var outer_key in apis) {
		(function(){
			var key = outer_key,
				api = apis[key],
				tw = document.createElement('script'),
				s, script_source = '';

			script_source += 'https://alpha-api.app.net/stream/0/users/@';
			script_source += api.screen_name;
			script_source += '/posts?';

			script_source += 'count=';
			script_source += api.count;
			script_source += '&';
		
			script_source += 'include_directed_posts=';
			script_source += ( api.exclude_replies == 't' ) ? '0' : '1';
		
			script_source += '&';
			script_source += 'include_deleted=';
			script_source += '0';

			script_source += '&';
			script_source += 'callback=RapidADN.callback.' + key + '';


			RapidADN.callback[key] = function(posts) {callback(api,posts);};

			tw.type = 'text/javascript';
			tw.async = true;
			tw.src = script_source;
			s = document.getElementsByTagName('script')[0]; 
			s.parentNode.insertBefore(tw, s);

		})();

	}
	
}(RapidADN, window, document);