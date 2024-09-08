<div class="modal fade" id="civilIdProof" tabindex="-1" aria-labelledby="civilIdProofLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="civilIdProofLabel">Civil ID Copy</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="proof-display">
                <div class="item">
                    <img id="photo_c1" src="{{ url('storage/images/'. $member->details->photo_civil_id_front) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}" class="image-fluid zoom-photo"  />
                </div>
                <div class="item">
                    <img id="photo_c2" src="{{ url('storage/images/'. $member->details->photo_civil_id_back) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}"  class="image-fluid zoom-photo" />
                </div>
                
            </div>
        </div>
        <div class="modal-footer">
            <div class="proof-text">
                Civil ID: {{ $member->details->civil_id }}
            </div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
</div>

<div class="modal fade" id="passportProof" tabindex="-1" aria-labelledby="passportProofLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="passportProofLabel">Passport Copy</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="proof-display">
                <div class="item">
                    <img id="photo_p1" src="{{ url('storage/images/'. $member->details->photo_passport_front) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}"  class="image-fluid zoom-photo" />
                </div>
                <div class="item">
                    <img id="photo_p2" src="{{ url('storage/images/'. $member->details->photo_passport_back) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}"  class="image-fluid zoom-photo" />
                </div>
          </div>
        </div>
        <div class="modal-footer">
            <div class="proof-text">
                <span>Passport No.:{{ $member->details->passport_no }}</span>  
                <span>Passport Expiry: {{ date('M d, Y', strtotime($member->details->passport_expiry)) }}</span>
            </div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
</div>