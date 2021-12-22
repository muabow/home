<script type="text/javascript">
		var gEqParams = {
						row : 10, // 열 수
						col : 1, // 단 수
						speed : 20, //스피드 백라이트 큐브
						freq : 500, // 신호 주파수
						on : true,	//기본적으로 활성화 (true,false)
						name : null
		};

		(function($) {
			var methods = {
				init : function(options) {
					var params = gEqParams;

					if (options) {
						$.extend(params, options);
					}

					var arrName = params.name.split("_");

					var eqWrap = $(this).addClass(arrName[0]);

					var neqCols = 'eqCol_' + arrName[0];
					var neqItem = 'eqItem_' + arrName[0];

					this.index = arrName[1];

					var eqCols = '.' + neqCols;
					var eqItem = '.' + neqItem;

					for ( c = 0; c < params.col; c++) {
						var eqColEl = $('<div>').addClass(neqCols).appendTo(eqWrap);
						for ( r = 0; r < params.row; r++) {
							$('<div>').addClass(neqItem).appendTo(eqColEl);
						}
					}
					var eqCol = $(eqCols, eqWrap), eqItem = $(eqItem, eqWrap), randomNumber = function(m, n) {
						m = parseInt(m);
						n = parseInt(n);
						return Math.floor(Math.random() * (n - m + 1)) + m;

					}, eqUp = function(colEl, val) {
						var speed = params.speed, v = params.row - val, i = params.row, j = 0, flag2 = true, eachItemUp = function() {
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
										eqDown(colEl, val);
									}
								} else {
									i--;
									j++;
									if (i > v) {
										eachItemUp()
									}
								}
							})
						}
						eachItemUp()

					}, eqDown = function(colEl, val) {
						var v = params.row - val, i = (params.row - val), j = 0, speed = params.speed * 2, eachItemDown = function() {
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

					}, eqInterval = function() {
						eqCol.each(function() {
							// eqUp($(this), randomNumber(0, params.row));
							var volume = parseInt($(".level_outputVolume").html()) + 1;

							eqUp($(this), (volume));
						})
					}
					eqInterval();

					if (params.on) {
						var eqIntervalId = setInterval(eqInterval, params.freq)
						$(this).data({
							'eqIntId' : eqIntervalId,
							'eqInt' : eqInterval,
							'freq' : params.freq,
							'on' : params.on
						})
					} else {
						$(this).data({
							'eqIntId' : eqIntervalId,
							'eqInt' : eqInterval,
							'freq' : params.freq,
							'on' : params.on
						})
					}
				}
			};

			$.fn.liEqualizer = function(method) {
				if (methods[method]) {
					return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));

				} else if ( typeof method === 'object' || !method) {
					return methods.init.apply(this, arguments);

				} else {
					$.error('method ' + method + ' jQuery.liEqualizer failed');
				}
			};
		})(jQuery);

		function initEqulizer(_name) {
			$('.' + _name).liEqualizer({
				row   : gEqParams.row,
				col   : gEqParams.col,
				speed : gEqParams.speed,
				freq  : gEqParams.freq,
				on    : gEqParams.on,
				volume: $(".level_" + _name).html(),
				name  : _name
			});
		}

</script>
