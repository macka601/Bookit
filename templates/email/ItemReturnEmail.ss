<p>
	<% sprintf(_t('ItemBookingCancelEmail.HI',"Hi %s,"),$Nickname) %>
</p>

<p>
	<% sprintf(_t('ItemBookingCancelEmail.MESSAGE',"This is just an email to notify you that you have returned the %s that was booked from the <b>%s</b> to the <b>%s</b>
	<br>
	If you would like to book it again, please head back to the website and use the booking system to book it again"),$Item, $ItemBookedFrom,$ItemBookedTo) %>
	</p>

<p>
	<% _t('ItemBookingCancelEmail.SALUTATION', "Thanks,<br/>The Gear Team.") %>
</p>

<p><% _t('ItemBookingCancelEmail.AUTOMATED_EMAIL', "NOTE: This is an automated email, any replies you make may not be read.") %></p>