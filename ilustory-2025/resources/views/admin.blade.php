<script>
    var ajaxUrl = '{{ $ajaxUrl }}';
</script>
<div class="wrap">
    <h1>
        Seznam přihlášek
    </h1>
    <h2>
        Platné přihlášky
    </h2>
    @if (count($records['verified']) > 0)
        <a href="{{ $ajaxUrl }}?action=download_stories" style="margin-bottom:4px;" class="button" download>
            Stáhnout
        </a>
        <div class="contest-table-container">
            <table class="contest-table">
                <thead>
                    <tr>
                        @foreach ($headers as $header)
                            <th>
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records['verified'] as $work)
                        <tr>
                            <td>
                                {{ $work['id'] }}
                            </td>
                            <td>
                                {{ $work['fullname'] }}
                            </td>
                            <td>
                                {{ $work['email'] }}
                            </td>
                            <td>
                                {{ $work['phone'] != 0 ? $work['phone'] : '' }}
                            </td>
                            <td>
                                <a href="{{ $work['work_url'] }}" target="_blank">
                                    {{ $work['work_name'] }}
                                </a>
                            </td>
                            <td>
                                {{ $work['age'] }}
                            </td>
                            <td>
                                {{ $work['note'] }}
                            </td>
                            <td>
                                {{ $work['created_at'] }}
                            </td>
                            <td>
                                @if (current_user_can('manage_writing_contest_records'))
                                    <button type="button"
                                        onclick="changeRecordStatus({{ $work['id'] }}, '{{ addslashes($work['work_name']) }}', 'invalid')">
                                        Vyřadit
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <h2 style="margin-top: 40px;">
        Přihlášky čekající na ověření
    </h2>
    @if (count($records['unverified']) > 0)
        <div class="contest-table-container">
            <table class="contest-table">
                <thead>
                    <tr>
                        @foreach ($headers as $header)
                            <th>
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records['unverified'] as $work)
                        <tr>
                            <td>
                                {{ $work['id'] }}
                            </td>
                            <td>
                                {{ $work['fullname'] }}
                            </td>
                            <td>
                                {{ $work['email'] }}
                            </td>
                            <td>
                                {{ $work['phone'] != 0 ? $work['phone'] : '' }}
                            </td>
                            <td>
                                <a href="{{ $work['work_url'] }}" target="_blank">
                                    {{ $work['work_name'] }}
                                </a>
                            </td>
                            <td>
                                {{ $work['age'] }}
                            </td>
                            <td>
                                {{ $work['note'] }}
                            </td>
                            <td>
                                {{ $work['created_at'] }}
                            </td>
                            <td>
                                @if (current_user_can('manage_writing_contest_records'))
                                    <button type="button"
                                        onclick="verify({{ $work['id'] }}, '{{ addslashes($work['work_name']) }}')">
                                        Ověřit
                                    </button>
                                @endif

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <h2 style="margin-top: 40px;">
        Vyřazené přihlášky
    </h2>
    @if (count($records['invalid']) > 0)
        <div class="contest-table-container">
            <table class="contest-table">
                <thead>
                    <tr>
                        @foreach ($headers as $header)
                            <th>
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records['invalid'] as $work)
                        <tr>
                            <td>
                                {{ $work['id'] }}
                            </td>
                            <td>
                                {{ $work['fullname'] }}
                            </td>
                            <td>
                                {{ $work['email'] }}
                            </td>
                            <td>
                                {{ $work['phone'] != 0 ? $work['phone'] : '' }}
                            </td>
                            <td>
                                <a href="{{ $work['work_url'] }}" target="_blank">
                                    {{ $work['work_name'] }}
                                </a>
                            </td>
                            <td>
                                {{ $work['age'] }}
                            </td>
                            <td>
                                {{ $work['note'] }}
                            </td>
                            <td>
                                {{ $work['created_at'] }}
                            </td>
                            <td>
                                @if (current_user_can('manage_writing_contest_records'))
                                    <button type="button"
                                        onclick="changeRecordStatus({{ $work['id'] }}, '{{ addslashes($work['work_name']) }}', 'valid')">
                                        Přesunout mezi platné
                                    </button>
                                    <button type="button"
                                        onclick="deleteRecord({{ $work['id'] }}, '{{ addslashes($work['work_name']) }}')">
                                        Odstranit
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
