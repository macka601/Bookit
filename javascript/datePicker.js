
$(function() {	
		$( "#Form_bookItemForm_from" ).datepicker({
			dateFormat: 'dd/mm/yy',
			changeMonth: false,
			numberOfMonths: 1,
            beforeShowDay: function (date) {
				// array to hold the range
                dateRange = [];           
				
                // populate the array
				for(var i=0;i < ranges.length;i++){					
					for (var d = new Date(ranges[i]); d <= new Date(ranges[i+1]); d.setDate(d.getDate() + 1)) {						
						//console.log("date d is " + d + ", ranges high is " + ranges[i] + ", ranges low is " + ranges[i+1]);
						dateRange.push($.datepicker.formatDate('dd-mm-yy', d));						
					}
					i++;
				}
				
				var dateString = $.datepicker.formatDate('dd-mm-yy', date);				
                
				return [dateRange.indexOf(dateString) == -1];
            },
		    onClose: function( selectedDate ) {
				$( "#Form_bookItemForm_to" ).datepicker( "option", "minDate", selectedDate );
			}
		});		

	
    $( "#Form_bookItemForm_to" ).datepicker({
		dateFormat: 'dd/mm/yy',
		changeMonth: false,
		numberOfMonths: 1,
		beforeShowDay: function (date) {
			dateRange = [];           // array to hold the range
			
			// populate the array
			for(var i=0;i<ranges.length;i++){					
				for (var d = new Date(ranges[i]); d <= new Date(ranges[i+1]); d.setDate(d.getDate() + 1)) {						
					dateRange.push($.datepicker.formatDate('dd-mm-yy', d));					
				}
				i++;
			}

			var dateString = $.datepicker.formatDate('dd-mm-yy', date);
		
			return [dateRange.indexOf(dateString) == -1];
		},
		onClose: function( selectedDate ) {
			$( "#Form_bookItemForm_from" ).datepicker( "option", "maxDate", selectedDate );					
		}
    });
  });