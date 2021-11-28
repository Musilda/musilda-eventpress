var eventStartDatTime = document.querySelector('#event_start_date_time');		
if ( eventStartDatTime ) {
	bulmaCalendar.attach( eventStartDatTime, {
		type: 'datetime',
		dateFormat: 'dd.MM.yyyy',
		weekStart: 1
	});	
}	