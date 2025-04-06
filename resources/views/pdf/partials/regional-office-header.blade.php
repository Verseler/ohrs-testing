<header style="text-align: center; line-height: 1; margin-bottom: 12px;">
    <div>
        <img src="{{ public_path('images/denr10Logo.png') }}"
            style="display: inline-block; object-fit: contain; width: 90px; height: 90px;" />

        <div style="display: inline-block; margin-left: 10px; align-text: center;">
            <div>Republic of the Philippines</div>
            <div>DEPARTMENT OF ENVIRONMENT AND NATURAL RESOURCES</div>
            <h3 style="font-size: 16px; margin: 0;">Regional Office</h3>
            <div>Brgy. Puntod, Cagayan de Oro City</div>
        </div>

        {{-- Spacer (Right side) --}}
        <div style="display: inline-block; width: 90px; height: 90px;"></div>
    </div>

    {{-- Divider --}}
    <hr style="width: 100%; margin-top: 4px; margin-bottom: 2px;" />

    <div style="display: block; margin-top: 5px;">
        <h1 style="font-size: 20px; margin: 0;">ONLINE HOSTEL RESERVATION SYSTEM</h1>
        <div>Monthly Report for {{ \Carbon\Carbon::parse($selectedDate ?? now())->format('F Y') }}</div>
        <div>Generated on: {{ \Carbon\Carbon::now()->format('F j, Y h:i A') }}</div>
    </div>
</header>