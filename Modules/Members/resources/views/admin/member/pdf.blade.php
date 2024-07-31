<table border="1" cellpadding="5px" width="100%" style="border: 1px solid #ddd; border-collapse:collapse">
    <tr>
        <td colspan="2"><h2>{{ $data['title'] }}</h2></td>
    </tr>
    <tr>
        <td style="width:200px">Name</td>
        <td>{{ $data['member']->name }}</td>
    </tr>
    <tr>
        <td>Email</td>
        <td>{{ $data['member']->user->email }}</td>
    </tr>
    <tr>
        <td>Phone</td>
        <td>{{ $data['member']->user->phone }}</td>
    </tr>
    <tr>
        <td>Civil ID</td>
        <td>{{ $data['member']->details->civil_id }} |  
            <a download="{{ str_replace(" ", "-", $data['member']->name).'_civil-id_front-'.$data['member']->details->photo_civil_id_front }}" href="{{ url('storage/images/'. $data['member']->details->photo_civil_id_front) }}">front</a> |
            <a download="{{ str_replace(" ", "-", $data['member']->name).'_civil-id_back-'.$data['member']->details->photo_civil_id_front }}" href="{{ url('storage/images/'. $data['member']->details->photo_civil_id_back) }}">back</a>
        </td>
    </tr>
    <tr>
        <td>Requested Member Type</td>
        <td>{{ Ucfirst($data['member']->type) }}</td>
    </tr>
    <tr>
        <td>Unit</td>
        <td>{{ Ucfirst($data['member']->details->member_unit->name) }}</td>
    </tr>
    <tr>
        <td>Requested Membership Type</td>
        <td>{{ Ucfirst($data['member']->membership->type) }}</td>
    </tr>
    <tr>
        <th colspan="2">Personal Details</th>
    </tr>
    <tr>
        <td>Gender</td>
        <td>{{ Ucfirst($data['member']->gender) }}</td>
    </tr>
    <tr>
        <td>Date of birth</td>
        <td>{{ date('M d, Y', strtotime($data['member']->details->dob)) }}</td>
    </tr>
    <tr>
        <td>Passport No.</td>
        <td>
            {{ $data['member']->details->passport_no }} |
            <a download="{{ str_replace(" ", "-", $data['member']->name).'_passport_front-'.$data['member']->details->photo_passport_front }}" href="{{ url('storage/images/'. $data['member']->details->photo_passport_front) }}">front</a> |
            <a download="{{ str_replace(" ", "-", $data['member']->name).'_passport_back-'.$data['member']->details->photo_passport_back }}" href="{{ url('storage/images/'. $data['member']->details->photo_passport_back) }}">back</a>
        </td>
    </tr>
    <tr>
        <td>Passport Expirty</td>
        <td>{{ $data['member']->details->passport_expiry }}</td>
    </tr>
    <tr>
        <td>Company</td>
        <td>{{ $data['member']->details->company }}</td>
    </tr>
    <tr>
        <td>Company</td>
        <td>{{ $data['member']->details->profession }}</td>
    </tr>
</table>