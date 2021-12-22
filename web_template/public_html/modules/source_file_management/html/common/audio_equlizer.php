<script type="text/javascript">


		class AudioEqulizerFunc {
			constructor(_name) {
				this.gEqParams = {
					row		: 11, 	// 열 수
					col		: 1, 	// 단 수
					speed	: 20, 	//animate speed, 20
					freq	: 500, 	//interval time, 500
					name	: _name,
					volume	: $(".level_" + _name).html()
				};

				this.liEqualizer($('.' + _name), this.gEqParams);
			}


			eqInterval(eqCol, eqItem, params) {
				var _self = this;
				eqCol.each(function() {
					var volume = parseInt($(".level_" + _self.gEqParams.name).html()) + 1;

					_self.eqUp($(this), volume, eqItem, params);
				})
			}

			liEqualizer(_this, method) {
				if ( typeof method === 'object' || !method) {
					this.init(_this, method);
				} else {
					$.error('method ' + method + ' jQuery.liEqualizer failed');
				}
			};

			//no use
			randomNumber(m, n) {
				m = parseInt(m);
				n = parseInt(n);
				return Math.floor(Math.random() * (n - m + 1)) + m;
			}

			eqUp(colEl, val, eqItem, params) {
				var _self = this;

				var speed = params.speed;
				var v = params.row - val;
				var i = params.row;
				var j = 0;
				var flag2 = true
				var eachItemUp = function() {
					$(eqItem, colEl).eq(i - 1).nextAll().stop().css({
						opacity : '1'
					});
					if ($(eqItem, colEl).eq(i - 1).css('opacity') == 1) {
						flag2 = false
					} else {
						flag2 = true
					}
					$(eqItem, colEl).eq(i - 1).stop(true).animate({
						opacity : '1'
					}, params.speed, function() {
						if ($(eqItem, colEl).index(this) == v) {
							if (flag2) {
								_self.eqDown(colEl, val, eqItem, params);
							}
						} else {
							i--;
							j++;
							if (i > v) {
								eachItemUp();
							}
						}
					})
				}
				eachItemUp();

			}

			eqDown(colEl, val, eqItem, params) {
				var i = params.row - val;
				var j = 0;
				var speed = params.speed * 2;
				var eachItemDown = function() {
					if (i == (params.row - val)) {
						$(eqItem, colEl).eq(i).animate({
							opacity : '0.2'
						}, speed * 10)
						setTimeout(function() {
							i++;
							j++;
							if (i < params.row) {
								eachItemDown();
							}
						}, speed)
					} else {
						$(eqItem, colEl).eq(i).animate({
							opacity : '0.2'
						}, speed, function() {
							i++;
							j++;
							if (i < params.row) {
								eachItemDown();
							}
						})
					}
				}
				eachItemDown();
			}

			init(_this, options) {
				var _self  = this;
				var params = this.gEqParams;

				if (options) {
					$.extend(params, options);
				}

				var arrName = params.name.split("_");

				var eqWrap = _this.addClass(arrName[0]);

				var neqCols = 'eqCol_' + arrName[0];
				var neqItem = 'eqItem_' + arrName[0];

				_this.index = arrName[1];

				var eqCols = '.' + neqCols;
				var eqItem = '.' + neqItem;

				for ( var c = 0; c < params.col; c++) {
					var eqColEl = $('<div>').addClass(neqCols).appendTo(eqWrap);
					for ( var r = 0; r < params.row; r++) {
						$('<div>').addClass(neqItem).appendTo(eqColEl);
					}
				}

				var eqCol = $(eqCols, eqWrap);
				var eqItem = $(eqItem, eqWrap);

				var eqIntervalId = setInterval(function() {
					_self.eqInterval(eqCol, eqItem, params);
				}, params.freq);
			}
		}

</script>
