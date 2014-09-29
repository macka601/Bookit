
<% with Item %>

<div class="ItemsPageContainer">

	<div class="ItemsPage_ItemPhoto">
		<a href=$itemPhoto.Link data-lightbox="$Item" data-title="$Item">$itemPhoto.setWidth(150)</a>
	</div>
	
	<div class="ItemsPage_ItemTitle">
		<h1>$Item</h1>				
	</div> <!-- .itemTitle-->							

	<div class="ItemsPage_ItemDescription">
		<p>$Description</p>
		<div class="ItemsPage_MaximumBookingTime">
			<p>Please note - this item has a maximum booking period of $MaxBookOutPeriod days</p>
		</div>
	</div> <!-- .Description-->	

<% end_with %>
<% if $CurrentMember %>	
	<div id="bookItemForm">
		$bookItemForm	
	</div>
<% end_if %>		
</div>
<% if $CurrentMember %>	
	<div class="Message">$Message</div>
	
	<% if showUserHistoryWithItem %>
		<div class="Table_userHistory"> 
			<div class="Table_userHistory_row">
				<div class="Table_userHistory_column">Booked From</div>
				<div class="Table_userHistory_column">Booked To</div>
				<div class="Table_userHistory_column">Item Returned</div>
			</div>
			<% loop showUserHistoryWithItem %>
				<% if $Odd %><div class="Table_userHistory_row_odd"><% else %><div class="Table_userHistory_row"><% end_if %>
					<div class="Table_userHistory_column">$itemBookingFrom</div>
					<div class="Table_userHistory_column">$itemBookingTo</div>
					<div class="Table_userHistory_column"><% if $itemReturned==0 %><a href="$ReturnItemLink">No</a><% else %>$itemReturnDate<% end_if %></div>
					<div class="Table_userHistory_column"><a href="$CancelBookingLink">Cancel</a></div>
				</div>
			<% end_loop %>
		</div>
		<script>
			var ranges = [ <% loop $getItemBookedOutDates %> "$itemBookingFrom", "$itemBookingTo" <% if $Last %><% else %>,<% end_if %> <% end_loop %> ];
		</script>
	<% end_if %>	
<% else %>
	<div class="loginForm">
		<p>Please Login to book the item</p>
		$Form
	</div>
<% end_if %>	