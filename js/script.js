/**
 * Created by Constantine on 01.09.14.
 */
$(function(){

    function sortByValue(data){
        var dataArray = [];
        for (key in data) {
            var value = data[key];
            dataArray.push({key: parseInt(key), value: value});
        }

        dataArray.sort(function(a, b){
            if (a.value < b.value) return -1;
            if (b.value < a.value) return 1;
            return 0;
        });

        return dataArray;
    }

    function fillOptions(selectId, param) {

		var d = $.Deferred();
		selectId = '#' + selectId;

		$(selectId).attr('disabled', true);

		var data = {};
		data[param] = $(this).val();
		$.getJSON('/ajax.php', data, function(r) {

			$(selectId + ' option').not('.no-option').remove();
            var r = sortByValue(r);

            for(var i = 0; i < r.length; i ++) {
                var id = r[i]['key'];
                var name = r[i]['value'];

                $(selectId).append(
                    $("<option>")
                        .attr('value', id)
                        .text(name)
                );
            }

			$(selectId).attr('disabled', false);
			d.resolve();
		});

		return d;
	}

	$('#BRAND').change(function(){
		fillOptions.call(this, 'MODEL', 'brand_id');
	});

	if($('#BRAND').val() > 0) {
		fillOptions.call($('#BRAND'), 'MODEL', 'brand_id')
			.done(function(){
				$('#MODEL').val($('#modelValue').val());

				//fillOptions.call($('#MODEL'), 'BODY', 'model_id')
				//	.done(function(){
				//		$('#BODY').val($('#bodyValue').val());
				//	});
			});
	}

	//$('#MODEL').change(function(){
	//	fillOptions.call(this, 'BODY', 'model_id');
	//});

	var bhSpares = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		limit: 20,
		prefetch: {
			url: '/ajax.php?action=spares-prefetch',
			filter: function(list) {
				return $.map(list, function(spare) { return { name: spare }; });
			}
		}
	});
	bhSpares.initialize();
	$('.typeahead.name').typeahead(null, {
        name: 'spares',
		displayKey: 'name',
		source: bhSpares.ttAdapter()
	});

	var bhEngines = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		limit: 20,
		prefetch: {
			url: '/ajax.php?action=spares-engine-prefetch',
			filter: function(list) {
				return $.map(list, function(engine) { return { name: engine }; });
			}
		}
	});
	bhEngines.initialize();
	$('.typeahead.engine').typeahead(null, {
		name: 'engines',
		displayKey: 'name',
		source: bhEngines.ttAdapter()
	});

    var bhBodies = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        limit: 20,
        prefetch: {
            url: '/ajax.php?action=spares-body-prefetch',
            filter: function(list) {
                return $.map(list, function(body) { return { name: body }; });
            }
        }
    });
    bhBodies.initialize();
    $('.typeahead.body').typeahead(null, {
        name: 'bodies',
        displayKey: 'name',
        source: bhBodies.ttAdapter()
    });

	$('.filterCarOverlay img').load(function(){
		$('.filterCarOverlay').fadeIn();
	});

	$('.left-right input, .front-rear input').change(function(e){
		var s = '';
		if($('.left-right :checked').val() == 'l'){
			s = 'l';
		} else if($('.left-right :checked').val() == 'r'){
			s = 'r';
		}

		if($('.front-rear :checked').val() == 'f'){
			s += 'f';
		} else if($('.front-rear :checked').val() == 'r'){
			s += 're';
		}

		if(s.length > 0){
			$('.filterCarOverlay').fadeOut(function(){
				$('.filterCarOverlay img').attr('src', '/images/vehicle-' + s + '.png');
			});
		}
		else {
			$('.filterCarOverlay').fadeOut();
		}
	});
});