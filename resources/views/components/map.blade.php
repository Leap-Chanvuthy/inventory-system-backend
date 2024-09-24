<div id="map" style="height: 400px;"></div>
<input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
<input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">

<script>
    function initMap() {
        const cambodia = { lat: 12.5657, lng: 104.9910 };
        const map = new google.maps.Map(document.getElementById('map'), {
            zoom: 6,
            center: cambodia,
        });

        const marker = new google.maps.Marker({
            position: cambodia,
            map: map,
        });

        google.maps.event.addListener(map, 'click', function(event) {
            placeMarker(event.latLng, map);
        });

        function placeMarker(location, map) {
            marker.setPosition(location);
            document.getElementById('latitude').value = location.lat();
            document.getElementById('longitude').value = location.lng();
        }
    }
</script>

<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAd4rEAQqf58fCJGABqW99teDP9BcuyN08&callback=initMap">
</script>
