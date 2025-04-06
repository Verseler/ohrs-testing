{{-- Email Format --}}
<!DOCTYPE html>
<html>

<body style="font-family: sans-serif; color: #333;">
    <h2>Reservation Confirmation</h2>
    <p>
        Thank you for using the <strong>Online Hostel Reservation System (OHRS)</strong>.
    </p>
    <p>
        <strong>Your Reservation Code:</strong><br>
        <span style="font-size: 20px; color: #2c7be5; font-weight: bold;">{{ $data['content'] }}</span>
    </p>
    <p>
        Please keep this code for your records. You’ll need it to view your reservation status.
    </p>
    <br>
    <p>
        Best regards,<br>
        DENR 10 - Online Hostel Reservation System
    </p>
</body>

</html>
