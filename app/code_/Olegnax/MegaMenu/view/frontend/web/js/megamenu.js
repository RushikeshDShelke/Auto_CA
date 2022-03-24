define([
	'jquery',
	'matchMedia',
	'jquery-ui-modules/widget',
	'jquery-ui-modules/core',
	'plugins/velocity',
	'plugins/scrollbar',
], function ($, mediaCheck) {
	'use strict';

	$.widget('ox.OxMegaMenu', {
		options: {
			direction: 'horizontal',
			positionHorizontal: 'left',
			btn: false,
			actionActive: 'active',
			header: '.container',
			toggleTransitionDuration: 400,
			mediaBreakpoint: 768,
			classNavigation: '.ox-megamenu-navigation',
			classDropdown: '.ox-megamenu__dropdown',
		},
		_alignFunctions: {
			'horizontal': {
				'container-left': function () {
					var $cont = $(this.options.header);
					if ($cont.length) {

						var cont_l = $cont[0].getBoundingClientRect().left,
							ox_megamenu_l = this.element[0].getBoundingClientRect().left;
						return {
							left: cont_l - ox_megamenu_l
						};
					} else {
						console.warn('Header container not found. Please set correct header selector in Olegnax / Megamenu / Configuration.')
					}
				},
				'container-right': function () {
					var $cont = $(this.options.header),
						cont_r = $cont[0].getBoundingClientRect().right,
						ox_megamenu_r = this.element[0].getBoundingClientRect().right;
					return {
						left: 'auto',
						right: ox_megamenu_r - cont_r
					};
				},
				'container-center': function () {
					var $cont = $(this.options.header),
						cont_l = $cont[0].getBoundingClientRect().left,
						cont_w = $cont.innerWidth(),
						ox_megamenu_l = this.element[0].getBoundingClientRect().left;

					return {
						left: cont_l - ox_megamenu_l + cont_w / 2
					};
				},
				'window-left': function () {
					var ox_megamenu_l = this.element[0].getBoundingClientRect().left;
					return {
						left: ox_megamenu_l * -1
					};
				},
				'window-right': function () {
					var windowWidth = window.innerWidth - this._scrollWidth,
						ox_megamenu_r = this.element[0].getBoundingClientRect().right;

					return {
						left: 'auto',
						right: (windowWidth - ox_megamenu_r) * -1
					};
				},
				'window-center': function () {
					var windowWidth = window.innerWidth - this._scrollWidth,
						ox_megamenu_l = this.element[0].getBoundingClientRect().left;

					return {
						left: ox_megamenu_l * -1 + windowWidth / 2
					};
				},
				'define': 'menu-left',
			},
			'vertical': {
				'container-left': function () {
					var $cont = $(this.options.cont),
						cont_l = $cont[0].getBoundingClientRect().left,
						ox_megamenu_l = this.element[0].getBoundingClientRect().left,
						ox_megamenu_w = this.element.innerWidth();

					return {
						left: 'auto',
						right: ox_megamenu_l - cont_l + ox_megamenu_w
					};
				},
				'container-right': function () {
					var $cont = $(this.options.cont),
						cont_r = $cont[0].getBoundingClientRect().right,
						ox_megamenu_r = this.element[0].getBoundingClientRect().right,
						ox_megamenu_w = this.element.innerWidth();

					return {
						left: cont_r - ox_megamenu_r + ox_megamenu_w
					};
				},
				'define': 'container-left',
			}
		},
		_create: function () {

			var _self = this,
				$ox_megamenu = this.element,
				$ox_megamenu__navigation = $ox_megamenu.find(this.options.classNavigation),
				$ox_megamenu__dropdown = $ox_megamenu.find(this.options.classDropdown),
				$ox_megamenu__submenu = $ox_megamenu__dropdown.find('.ox-submenu');
			this._getScrollWidth();

			mediaCheck({
				media: '(min-width:' + this.options.mediaBreakpoint + 'px)',
				entry: $.proxy(function () {
					this._toggleDesktopMode();
				}, this),
				exit: $.proxy(function () {
					this._toggleMobileMode();
				}, this),
			});

			this._widthHandlers({
				elem: $ox_megamenu__navigation,
				delegate: '>li, li.ox-dropdown--megamenu',
				namespace: 'togglemegamenu',
				desktop: true,
				events: {
					'mouseenter': function (e) {
						_self._showMM(this);
					},
					'mouseleave': function (e) {
						_self._hideMM(this);
					},
					'touchstart': function (e) {
						if ($(this).hasClass('ox-megamenu--opened') || ($(this).find('> a').data('url') == 'custom' && !$(this).hasClass('parent'))) {
							return;
						}
						e.preventDefault();
						_self._showMM(this);
					}
				}
			});

			//dropdown handlers
			this._widthHandlers({
				elem: $ox_megamenu__submenu.parent('li'),
				namespace: 'toggledropdown',
				desktop: true,
				events: {
					'mouseenter': function (e) {
						_self._showDD(this);
					},
					'mouseleave': function (e) {
						_self._hideDD(this);
					},
					'touchstart': function (e) {
						if ($(this).hasClass('js-touch')) {
							return;
						}
						$(this).addClass('js-touch');
						e.preventDefault();
						_self._showDD(this);
					}
				}
			});

			this._widthHandlers({
				elem: $ox_megamenu.find('li.parent > a, li.ox-dropdown--megamenu > a'),
				namespace: 'togglelist',
				desktop: false,
				events: {
					'click': function (e) {
						if (('A' === e.target.tagName || 'SPAN' === e.target.tagName) && $(this).parent().hasClass('ox-megamenu--opened') || !$(this).closest('li').eq(0).find('.submenu, .ox-submenu, .ox-megamenu__dropdown').length)
							return;
						_self._toggleList(this);

						e.preventDefault();
						return false;
					},
				}
			});

			$(this.window).trigger('resize');
		},
		_getScrollWidth: function () {
			var $tempElement = $('<div>').css({
				overflowY: 'scroll',
				width: '50px',
				height: '50px',
				visibility: 'hidden'
			});
			$('body').append($tempElement);
			this._scrollWidth = $tempElement[0].offsetWidth - $tempElement[0].clientWidth;
			$tempElement.remove();
		},
		_toggleDesktopMode: function () {
			this.is_desktop = true;
			this._hideAllMM();
			this.element.find('.ox-megamenu--opened').removeClass('ox-megamenu--opened');
		},
		_toggleMobileMode: function () {
			this.is_desktop = false;
			this._hideAllMM();
			var dropdown = this.element.find('.ox-megamenu__dropdown');
			dropdown.css({'width': '', 'left': ''});
			dropdown.find('.ox-megamenu-list').first().css({'width': ''});
			dropdown.find('.ox-megamenu-block-left, .ox-megamenu-block-right').css({'width': ''});
		},
		_hideAllMM: function () {
			this._hideMM(this.element.find(this.options.classNavigation).find('> li'), true);
		},
		_toggleList: function (btn) {
			var _self = this,
				$ox_megamenu = this.element,
				$btn = $(btn),
				$li = $btn.parent(),
				$lvl = $li.find('> *:not(a)');

			$lvl.css('height', '');
			$lvl.velocity('stop');

			if ($li.hasClass('ox-megamenu--opened')) {
				var $li_other = $li.find('.ox-megamenu--opened');

				$li.removeClass('ox-megamenu--opened');

				$lvl.velocity('slideUp', {
					duration: _self.options.toggleTransitionDuration,
					complete: function () {
						$li_other.removeClass('ox-megamenu--opened').removeAttr('style');
						$li_other.find('ul').removeAttr('style');
                                                if($ox_megamenu.hasClass('ps-enabled')){
                                                    $ox_megamenu.perfectScrollbar('update');
                                                }
					}
				});
			} else {
				$lvl.velocity('slideDown', {
					duration: _self.options.toggleTransitionDuration,
					complete: function () {
                                            if($ox_megamenu.hasClass('ps-enabled')){
						$ox_megamenu.perfectScrollbar('update');
                                            }
					}
				});
				$li.addClass('ox-megamenu--opened');
				$('body').trigger('contentUpdated');
			}
		},
		_widthHandlers: function (obj) {
			var _self = this;

			$(window).on('resize.handlerswidth', function () {
				if (obj.desktopBp === _self.is_desktop)
					return; 
				else
					obj.desktopBp = _self.is_desktop; 

				var $elem = $(obj.elem),
					ns = obj.namespace ? '.' + obj.namespace : '';

				if (obj.desktop === _self.is_desktop) {
					$.each(obj.events, function (key, val) {
						$elem.on(key + ns, obj.delegate, val);
					});
				} else {
					$.each(obj.events, function (key) {
						$elem.unbind(key + ns);
					});
				}
			});
		},
		_showMM: function (element) {
			var _self = this,
				$element = $(element),
				$mm = $element.children('.ox-megamenu__dropdown'),
				$mmp = $mm.parents('.ox-megamenu__dropdown').add($mm),
				$ox_megamenu__dropdown = this.element.find('.ox-megamenu__dropdown');

			if (!$mm.length)
				return;

			$mm.unbind('transitionend.mmclose');
			$ox_megamenu__dropdown.not($mmp).removeClass('opened animate');

			if (this.options.beforeOpenMM)
				this.options.beforeOpenMM($mm, $element);

			this.currentButton = $element;
			this.currentMegamenu = $mm;

			$element.addClass('ox-megamenu--opened');
			$mm.addClass('opened');

			this._beforeOpenMM();

			$mm.one('transitionend.mmopen', function () {
				_self._afterOpenMM();
				if (_self.options.afterOpenMM)
					_self.options.afterOpenMM($mm, $element);
			});
			$mm.find('.owl-carousel:not(.owl-mm-reloaded), .owl-loaded:not(.owl-mm-reloaded)').trigger('refresh.owl.carousel').addClass('owl-mm-reloaded');
			$mm.addClass('animate');
		},
		_hideMM: function (btn, is_fast) {

			var _self = this,
				$btn = $(btn),
				$mm = $btn.find('.ox-megamenu__dropdown');
			if (!$mm.length)
				return;

			$mm.unbind('transitionend.mmopen');

			if (this.options.beforeCloseMM)
				this.options.beforeCloseMM($mm, $btn);

			this.currentButton = $btn;
			this.currentMegamenu = $mm;

			this._beforeCloseMM();

			$mm.one('transitionend.mmclose', function () {
				$mm.removeClass('opened');
				$btn.removeClass('ox-megamenu--opened');
				_self._afterCloseMM();
				if (_self.options.afterCloseMM)
					_self.options.afterCloseMM($mm, $btn);
			});
			$mm.removeClass('animate');

			if (is_fast)
				$mm.trigger('transitionend.mmclose');
		},
		_beforeOpenMM: function () {
			var _self = this;

			function setWidth() {
				var mmWidth = _self.currentMegamenu.data('ox-mm-w'),
					w_funcs = _self._widthFunctions[_self.options.direction],
					set_w;

				if (w_funcs.hasOwnProperty(mmWidth))
					set_w = w_funcs[mmWidth].apply(_self, arguments);
				else if ($.isNumeric(mmWidth))
					set_w = mmWidth;

				if (set_w) {
					if (mmWidth === 'column-max-width') {
						var mm_list = _self.currentMegamenu.find('.ox-megamenu-list').first();
						var mm_block_left = _self.currentMegamenu.find('.ox-megamenu-block-left');
						var mm_block_right = _self.currentMegamenu.find('.ox-megamenu-block-right');
						mm_list.innerWidth(set_w);
						mm_block_left.innerWidth(set_w);
						mm_block_right.innerWidth(set_w);
						_self.currentMegamenu.css('width', 'auto');
					} else {
						_self.currentMegamenu.innerWidth(set_w);
					}
				}
			}
			function setAlign(attr) {
				var align = _self.currentButton.data(attr),
					align_func = _self._alignFunctions[_self.options.direction],
					align = align || align_func['define'],
					css;
				if (align_func.hasOwnProperty(align)) {
					css = align_func[align].apply(_self);
					if (css) {
						_self.currentMegamenu.css(css);
					}
				}
			}
			function checkWidth() {
				var $mm = _self.currentMegamenu,
					mmLeft = $mm[0].getBoundingClientRect().left,
					mmRight = $mm[0].getBoundingClientRect().right,
					mmWidth = $mm.innerWidth(),
					windowWidth = window.innerWidth - _self._scrollWidth;

				if (mmWidth > windowWidth) {
					if (mmLeft > 0) {
						$mm.innerWidth(windowWidth - mmLeft);
					} else if (mmRight < windowWidth) {
						$mm.innerWidth(windowWidth - (windowWidth - mmRight));
					} else {
						$mm.innerWidth(windowWidth);
					}
				} else if (mmLeft < 0) {
					$mm.innerWidth(mmWidth + mmLeft);
				} else if (mmRight > windowWidth) {
					$mm.innerWidth(mmWidth - (mmRight - windowWidth));
				}
			}
			setWidth();

			setAlign('ox-mm-a-h');

			checkWidth();

			this._toggleTransition('show');
		},
		_afterOpenMM: function () {
			this._checkWindowLimit();
			this._toggleTransition('show');
		},
		_beforeCloseMM: function () {
			this._toggleTransition('hide');
		},
		_afterCloseMM: function () {
			var $mm = this.currentMegamenu;

			// $mm.removeAttr('style');
			$mm.css('max-height', '');
			if ($mm.hasClass('ox-megamenu--scroll')) {
				$mm.removeClass('ox-megamenu--scroll');
				$mm.perfectScrollbar('destroy').removeClass('ps');
			}
		},
		_widthFunctions: {
			'horizontal': {
				'menu': function () {
					return this.element.innerWidth();
				},
				'fullwidth': function () {
					return window.innerWidth - this._scrollWidth;
				},
				'container': function () {
					return $(this.options.header).innerWidth();
				},
				'column-max-width': function () {
					var mm_cw = this.currentMegamenu.data('ox-mm-cw'),
						mm_col = this.currentMegamenu.data('ox-mm-col');
					if (mm_cw) {
						if (mm_col) {
							return mm_cw * mm_col;
						} else {
							return mm_cw;
						}
					}
				},
				'custom': function (mm) {
					return this.currentMegamenu.data('ox-mm-cw');
				},
			},
			'vertical': {
				'fullwidth': function () {
					switch (this.options.positionHorizontal) {
						case 'right':
							return window.innerWidth - this._scrollWidth - (window.innerWidth - this._scrollWidth - this.element[0].getBoundingClientRect().left);
						default:
							return window.innerWidth - this.element[0].getBoundingClientRect().right - this._scrollWidth;
					}
				},
			},
		},
		_toggleTransition: function (act) {
			var $btn = this.currentButton,
				$mm = this.currentMegamenu,
				$trns = $btn.find('.ox-megamenu-trns');

			switch (act) {
				case 'show':
					var set_obj = {},
						btn_pos = $btn[0].getBoundingClientRect(),
						mm_pos = $mm[0].getBoundingClientRect();

					if (this.options.direction === 'horizontal') {
						set_obj.width = $mm.innerWidth();
						set_obj.height = mm_pos.top - btn_pos.bottom + 10;

						if (mm_pos.left > btn_pos.left) {
							set_obj.width += mm_pos.left - btn_pos.left;
						} else if (mm_pos.right < btn_pos.right) {
							set_obj.width += btn_pos.right - mm_pos.right;
							set_obj.left = (btn_pos.left - mm_pos.left) * -1;
						} else {
							set_obj.left = (btn_pos.left - mm_pos.left) * -1;
						}

					} else if (this.options.direction === 'vertical') {
						set_obj.height = $mm.innerHeight();

						if (this.options.positionHorizontal === 'left') {
							set_obj.width = mm_pos.left - btn_pos.right;

						} else if (this.options.positionHorizontal === 'right') {
							set_obj.width = btn_pos.left - mm_pos.right;
						}

						if (mm_pos.top > btn_pos.top) {
							set_obj.height += mm_pos.top - btn_pos.top;
						} else if (mm_pos.bottom < btn_pos.bottom) {
							set_obj.height += btn_pos.bottom - mm_pos.bottom;
							set_obj.top = (btn_pos.top - mm_pos.top) * -1;
						} else {
							set_obj.top = (btn_pos.top - mm_pos.top) * -1;
						}
					}

					$trns.css(set_obj);
					break;

				case 'hide':
					$trns.removeAttr('style');
					break;
			}
		},
		_checkWindowLimit: function () {
			if (this.options.direction !== 'horizontal' || !this.element.find(this.options.classNavigation).parent().hasClass('ps-enabled'))
				return;

			var $mm = this.currentMegamenu,
				mm_b = $mm[0].getBoundingClientRect().bottom,
				wind_h = window.innerHeight;

			if (mm_b > wind_h) {
				var mm_h = $mm.innerHeight();

				$mm.addClass('ox-megamenu--scroll');
				$mm.css({'max-height': mm_h - (mm_b - wind_h)});
				$mm.perfectScrollbar({
					suppressScrollX: true
				});
			}

		},
		_showDD: function (btn) {
			var $btn = $(btn),
				$dd = $btn.find('> ul'),
				$mm = this.currentMegamenu;

			$dd.unbind('transitionend.ddclose');
			$dd.addClass('opened');

			if ($mm.hasClass('ps') && $mm.hasClass('ox-megamenu-mm--simple')) {
				$mm.perfectScrollbar('update');
			} else {
				var dd_pos = $dd[0].getBoundingClientRect(),
					windowWidth = window.innerWidth - this._scrollWidth,
					dd_lim_l = (dd_pos.left - $dd.innerWidth() - $dd.parents('ul').first().innerWidth()) < 0,
					dd_lim_r = dd_pos.right > windowWidth,
					is_prnt_reverse = $dd.parents('ul').first().hasClass('ox-megamenu-dd--reverse');
				if (dd_lim_r || (is_prnt_reverse && !dd_lim_l))
					$dd.addClass('ox-megamenu-dd--reverse');
			}
			$dd.addClass('animate');
		},
		_hideDD: function (btn) {
			var $btn = $(btn),
				$dd = $btn.find('> ul'),
				$mm = this.currentMegamenu;

			$dd.one('transitionend.ddclose', function (e) {
				$dd.removeClass('opened ox-megamenu-dd--reverse').css('max-height', '');
				if ($mm.hasClass('ps'))
					$mm.perfectScrollbar('update');
			});
			$dd.removeClass('animate');
		},
		_setOption: function (key, value, is_attr) {
			if (is_attr && !value)
				return;

			$.Widget.prototype._setOption.apply(this, arguments);
		},
		destroy: function () {
			$.Widget.prototype.destroy.call(this);
		}
	});

	return $.ox.OxMegaMenu;
});
