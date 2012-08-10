$(function() {
	//Parse all templates
	var templates = {};
	var top = $("#jqt");
	var modal = $("#modal");
	var project = {};

	$("script.template").each(function() {
		var name = $(this).attr("id").replace(/^template-/, '');
		templates[name] = _.template($(this).html());
	});

	var insertPages = jQT.insertPages;


	$(document).on("click", ".volumelist li.title", function() {
		$(this).nextUntil(".title").slideToggle("fast");
		return false;	
	}).on("pageAnimationStart ", function() {
		
	}).on('click', ".thumb", function() {
		var winSize = [$(window).innerWidth(), $(window).innerHeight()];
		var size    = $(this).data("size");
		

		if(!$(this).hasClass("full")) {
			var clone = $(this).clone().addClass("full").hide().appendTo("body");

			//Make big
			var ratio = size[1] / size[0];

			var newSize = [winSize[0] -30, (winSize[0] -30) * ratio];

			if(newSize[1] > winSize[1]) {
				newSize[1] = winSize[1] - 30;
				newSize[0] = winSize[1] / ratio;
			}

			$(clone).css({
				width  : newSize[0],
				height : newSize[1],
				left   : (winSize[0] - newSize[0]) / 2,
				top   : (winSize[1] - newSize[1]) / 2
			}).fadeIn("fast");

		} else {
			//Make small
			$(this).remove();
		}
	}).on('click', '.project .header', function() {
		if($(this).hasClass("extended")) {
			$(this).stop(true).animate({maxHeight : 120}, 'fast').removeClass('extended');
		} else {
			$(this).stop(true).animate({maxHeight : $(this).children("li").outerHeight()}, 'fast').addClass('extended');
		}
	});
	function findNext(page) {
		var found  = false;
		var next = '';
		_.every(project.volumes, function(vol, i) {
			_.every(vol, function(chapter, link) {
				if(link == page) {
					//Bingo
					found = true;
				} else if(found) {
					next = link;
					return false;
				}
				return true;

			});
			if(found && next) {
				return false;
			} else return true;
		});
		return next;
	}

	function findPrev(page) {
		var prev = '';
		var found  = false;
		var keys = [];
		for(var k in project.volumes) keys.unshift(k);
		_.every(keys, function(i) {
			var vol = project.volumes[i];
			var kkeys = [];
			for(var k in vol) kkeys.unshift(k);
			_.every(kkeys, function(link) {
				if(link == page) {
					//Bingo
					found = true;
				} else if(found) {
					prev = link;
					return false;
				}
				return true;

			});
			if(found && prev) {
				return false;
			} else return true;
		});

		return prev;
	}

	var clickHandler = function() {
		var self = this;

		var url = window.location.origin + window.location.pathname;
		if(!this.href.replace(url, '').match(/^#/)) {
			modal.show();
			//We gotta fetch the page ourselves.
			//jQT.goTo(this.href);
			$.get(this.href)
				.done(function(res) {
					if(typeof res == "string") {
						//HTML - chapter text
						var page = self.href.replace(/^.+?page=/, '');
						var id = page.replace(/\W/g, '');


						var html = templates.chapter({id : id
													, body: res
													, next: findNext(page)
													, prev: findPrev(page)});
						var page = jQT.insertPages(html);

						page.find("img").each(function() {
							var img = $(this);
							var src = img.attr("src");
							var winSize = [$(window).width(), $(window).height()];
							var imgSize = [$(img).width(), $(img).height()];
							var newSize = [ winSize[0] / 4, (winSize[0] / 4) / imgSize[0] * imgSize[1] ];

							var thumb = img.closest(".thumb");
							if(!thumb.length) {
								var a = img.closest("a");
								var el = img;
								if(a.length) {
									el = a;
								}
								thumb = $("<div />").addClass("thumb").insertBefore(el);
							}
							thumb.data("size", newSize);

							thumb.css("width" , newSize[0]);
							thumb.css("height", newSize[1]);
							thumb.css("backgroundImage", "url("+src+")");

							img.remove();
					
						});

						self.href = "#"+id;
					} else {
						if(res.success) {
							var animation = null;
							if(res.template == 'project_page') {
								//It's dirty to do a check like that, but whatever.
								project = res.data;
								//animation = "slideleft";
							}
							var html = templates[res.template](res.data);
							var page = jQT.insertPages(html);
							self.href = "#"+page.attr("id");

						} else {
							//Show error page
						}
					}
				})
				.fail(function() {

				}).always(function() {
					modal.hide();
				});
			return false;	
		} else {
			return true;
		};
	};

	$("#jqt").on("tap click", "a", clickHandler);

	clickHandler.call({href : 'controller.php'});


});