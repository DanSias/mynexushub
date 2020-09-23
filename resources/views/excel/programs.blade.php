@php
    $months = [
        '',
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'
    ]
@endphp


<table>
    <thead>
        <tr>
            <th>
                Program Code
            </th>
            <th>
                Active
            </th>
            <th>
                Location
            </th>
            <th>
                BU
            </th>
            <th>
                Degree Type
            </th>
            <th>
                Degree Name
            </th>
            <th>
                Vertical
            </th>
            <th>
                Subvertical
            </th>
            <th>
                Spend Strategy
            </th>
            <th>
                Lifetime Value
            </th>
            <th>
                Marketing Started
            </th>
            <th>
                Program Length
            </th>
            <th>
                Credit Hours
            </th>
            <th>
                Tuition (In State)
            </th>
            <th>
                Tuition (Out of State)
            </th>
            <th>
                GPA Required
            </th>
            <th>
                Testing Required
            </th>
            <th>
                Transfer Credits
            </th>
        </tr>
    </thead>

    <tbody>
        @foreach($programs as $program)
            <tr>
                <td>
                    {{ $program->code }}
                </td>
                <td>
                    {{ $program->active }}
                </td>
                <td>
                    {{ $program->location }}
                </td>
                <td>
                    {{ $program->bu }}
                </td>
                <td>
                    {{ $program->type }}
                </td>
                <td>
                    {{ $program->name }}
                </td>
                <td>
                    {{ $program->vertical }}
                </td>
                <td>
                    {{ $program->subvertical }}
                </td>
                <td>
                    {{ $program->strategy }}
                </td>
                <td>
                    @if ($program->ltv)
                        ${{ number_format($program->ltv) }}
                    @endif
                </td>
                <td>
                    @if ($program->start_month > 0 && $program->start_year > 0)
                        {{ $months[$program->start_month] }} 
                    @endif
                    @if ($program->start_year > 0)
                        {{ $program->start_year }}
                    @endif
                </td>
                <td>
                    @if($program->requirements && $program->requirements->program_months > 0)
                        {{ $program->requirements->program_months }} 
                        @if($program->requirements->program_months > 0 && $program->requirements->program_months_max > 0)
                                - {{ $program->requirements->program_months_max }} 
                        @endif
                        Months
                    @endif
                </td>
                <td>
                    @if($program->requirements && $program->requirements->credit_hours > 0)
                        {{ $program->requirements->credit_hours }}
                        @if($program->requirements->credit_hours_max > 0)
                            - {{ $program->requirements->credit_hours_max }}
                        @endif
                    @endif
                </td>
                <td>
                    @if($program->requirements && $program->requirements->tuition_in_state)
                        ${{ number_format($program->requirements->tuition_in_state) }}
                    @endif
                </td>
                <td>
                    @if($program->requirements && $program->requirements->tuition_out_state > 0)
                        ${{ number_format($program->requirements->tuition_out_state) }}
                    @endif
                </td>
                <td>
                    @if($program->requirements && $program->requirements->gpa_required)
                        {{ number_format($program->requirements->gpa_required, 2) }}
                    @endif
                </td>
                <td>
                    @if ($program->requirements && $program->requirements->gmat == 'yes')
                        GMAT
                        @if ($program->requirements->gmat_waiver == 'yes')
                            (Waiver)
                        @endif
                    @endif
                    @if ($program->requirements && $program->requirements->gre == 'yes')
                        GRE
                        @if ($program->requirements->gre_waiver == 'yes')
                            (Waiver)
                        @endif
                    @endif
                </td>
                <td>
                    @if ($program->requirements && $program->requirements->transfer_credits_accepted == 'yes')
                        Accepted. 
                    @endif
                    @if ($program->requirements && $program->requirements->transfer_credits_details)
                        {{ $program->requirements->transfer_credits_details }}
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>