<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="margin:0; padding:20px; background:#ffffff; font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#333333; line-height:1.6;">

    <p style="margin:0 0 15px;">Dear Sir/Mam,</p>

    @if(!empty($reports) && count($reports) == 1)
    <p style="margin:0 0 15px;">
        Please find herewith attached <strong>{{ $title }} No. {{ $reports[0]['doc_no'] }}</strong>{{ !empty($reports[0]['doc_date']) ? ' dated ' . $reports[0]['doc_date'] : '' }}.
    </p>
    @elseif(!empty($reports) && count($reports) > 1)
    <p style="margin:0 0 10px;">
        Please find herewith attached the following <strong>{{ $title }}</strong> documents:
    </p>
    <ul style="margin:0 0 15px; padding-left:20px;">
        @foreach($reports as $rep)
            <li style="margin-bottom:5px;">No. <strong>{{ $rep['doc_no'] }}</strong>{{ !empty($rep['doc_date']) ? ' dated ' . $rep['doc_date'] : '' }}</li>
        @endforeach
    </ul>
    @else
    <p style="margin:0 0 15px;">
        Please find herewith attached <strong>{{ $title }} No. {{ str_replace('_', '/', $name) }}</strong>.
    </p>
    @endif

    <p style="margin:0 0 15px;">
        This is system generated Email. Please do not respond to this Email.
    </p>

    <p style="margin:0 0 25px;">
        For any kind of Queries / Clarifications, please call undersign.
    </p>

    <p style="margin:0 0 5px;">Thank you very much & warm regards,</p>
    <p style="margin:0 0 5px;">Customer support team</p>
    <p style="margin:0 0 20px;">
        <!-- {{ $company->phone_no ?? $company->mobile_no ?? '' }} -->
    </p>

    @if(isset($company))
        @if(!empty($company->company_logo))
            @php
                $isBinary = false;
                if (is_string($company->company_logo)) {
                    if (strpos($company->company_logo, 'uploads/') === 0 || strpos($company->company_logo, 'company/') === 0) {
                        $isBinary = false;
                    } else {
                        $isBinary = preg_match('~[^\x20-\x7E\t\r\n]~', $company->company_logo) > 0;
                    }
                } else {
                    $isBinary = true;
                }
            @endphp

            <div style="margin-bottom:5px;">
                @if($isBinary)
                    <img src="{{ $message->embedData($company->company_logo, 'logo.png', 'image/png') }}" alt="{{ $company->company_name }}" style="max-height:80px; object-fit:contain; display:block;">
                @else
                    @php
                        $logoPath = storage_path('app/public/' . $company->company_logo);
                    @endphp
                    @if(file_exists($logoPath))
                        <img src="{{ $message->embed($logoPath) }}" alt="{{ $company->company_name }}" style="max-height:80px; object-fit:contain; display:block;">
                    @endif
                @endif
            </div>
        @endif

        <p style="margin:0 0 10px; font-size:11px; font-weight:bold; color:#333333; font-style:italic; font-family:Arial, Helvetica, sans-serif;">
            An ISO/IEC 17025:2005 Accredited Lab
        </p>

        <p style="margin:0 0 5px; font-size:12px; color:#555555; font-style:italic; font-family:Arial, Helvetica, sans-serif; line-height:1.4;">
            <strong>Lab Address:</strong><br>
            {!! $company->address !!}{{ !empty($company->city) ? ', ' . $company->city : '' }}{{ !empty($company->state) ? ', ' . $company->state : '' }}{{ !empty($company->pin_code) ? ' - ' . $company->pin_code : '' }}
        </p>

        <p style="margin:0 0 5px; font-size:12px; color:#555555; font-style:italic; font-family:Arial, Helvetica, sans-serif;">
            <strong>Contact No.:-</strong> 
            <!-- {{ $company->phone_no ?? $company->mobile_no ?? '' }} -->
        </p>

        @if(!empty($company->email))
            <p style="margin:0 0 5px; font-size:12px; color:#555555; font-style:italic; font-family:Arial, Helvetica, sans-serif;">
                <strong>Email ID:-</strong> 
                <!-- <a href="mailto:{{ $company->email }}" style="color:#0288d1; text-decoration:none;">
                    {{ $company->email }}
                </a> -->
            </p>
        @endif

        @if(!empty($company->web_address))
            <p style="margin:0 0 15px; font-size:12px; color:#555555; font-style:italic; font-family:Arial, Helvetica, sans-serif;">
                <strong>Visit us at</strong> <a href="{{ $company->web_address }}" target="_blank" style="color:#0288d1; text-decoration:none;">{{ $company->web_address }}</a>
            </p>
        @endif
    @endif

    <p style="color:#2e7d32; font-style:italic; font-weight:bold; font-size:12px; margin-top:20px;  padding-top: 10px; font-family:Arial, Helvetica, sans-serif;">
        Please consider your environmental responsibility. Do not print this e-mail unless you really need to.
    </p>

</body>
</html>
