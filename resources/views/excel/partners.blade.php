<table>
    <thead>
        <tr>
            <th>
                Partner Code
            </th>
            <th>
                Partner Name
            </th>
            <th>
                URL
            </th>
            <th>
                School Type
            </th>
            <th>
                State
            </th>
            <th>
                Region
            </th>
            <th>
                Accreditation
            </th>
            <th>
                Accrediting Body
            </th>
            <th>
                Applicants
            </th>
            <th>
                Admissions
            </th>
            <th>
                Acceptance Rate
            </th>
            <th>
                Competitiveness
            </th>
            <th>
                2018-2019 Tuition
            </th>
            <th>
                US News Ranking
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach($partners as $partner)
            <tr>
                <td>
                    {{ $partner->code }}
                </td>
                <td>
                    {{ $partner->school }}
                </td>
                <td>
                    {{ $partner->url }}
                </td>
                <td>
                    {{ ucFirst($partner->type) }}
                </td>
                <td>
                    {{ $partner->state }}
                </td>
                <td>
                    {{ $partner->region }}
                </td>
                {{-- Accreditation Abbreviation --}}
                <td>
                    @php
                        if ($partner->accreditation) {
                            $acc = $partner->accreditation; 
                            $accText = $acc->abbreviation;
                        } else {
                            $accText = '';
                        }
                    @endphp
                    {{ $accText }}
                </td>
                {{-- Accrediting Body --}}
                <td>
                    @php
                        if ($partner->accreditation) {
                            $acc = $partner->accreditation; 
                            $accText = $acc->agency_name;
                        } else {
                            $accText = '';
                        }
                    @endphp
                    {{ $accText }}
                </td>
                {{-- Applicants --}}
                <td>
                    @php
                        if ($partner->admissions) {
                            $appObj = $partner->admissions; 
                            $app = $appObj->applicants;
                        } else {
                            $app = '';
                        }
                    @endphp
                    @if ($app > 0)
                        {{ $app }}
                    @endif
                </td>
                {{-- Admissions --}}
                <td>
                    @php
                        if ($partner->admissions) {
                            $appObj = $partner->admissions; 
                            $app = $appObj->admissions;
                        } else {
                            $app = '';
                        }
                    @endphp
                    @if ($app > 0)
                        {{ $app }}
                    @endif
                </td>
                {{-- Acceptance --}}
                <td>
                    @php
                        if ($partner->admissions) {
                            $appObj = $partner->admissions; 
                            $app = (float) $appObj->acceptance;
                        } else {
                            $app = '';
                        }
                    @endphp
                    @if ($app > 0)
                        {{ $app }}
                    @endif
                </td>
                {{-- Competitiveness --}}
                <td>
                    @php
                        if ($partner->admissions) {
                            $appObj = $partner->admissions; 
                            $app = $appObj->competitiveness;
                        } else {
                            $app = '';
                        }
                    @endphp
                    {{ $app }}
                </td>
                {{-- Tuition --}}
                <td>
                    @php
                        if ($partner->earnings) {
                            $appObj = $partner->earnings; 
                            $app = $appObj->{'price_2018-19_without_aid'};
                        } else {
                            $app = '';
                        }
                    @endphp
                    {{ $app }}
                </td>
                {{-- US News Ranking --}}
                <td>
                    @php
                        if ($partner->rank) {
                            $appObj = $partner->rank; 
                            $app = $appObj->rank . ' (' . $appObj->category . ')';
                        } else {
                            $app = '';
                        }
                    @endphp
                    {{ $app }}
                </td>
            </tr>
        @endforeach
    </tbody>