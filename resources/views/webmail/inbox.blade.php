@extends('webmail.layout')
@section('title', isset($currentFolder) ? $currentFolder : (isset($searchQuery) ? 'Search Results' : 'Inbox'))

@section('styles')
<style>
    .mail-list{height:100%;overflow-y:auto;background:var(--bg2);padding-bottom:2rem;}
    .mail-item{display:grid;grid-template-columns:65px 200px 1fr 100px 40px;align-items:center;padding:.75rem 1.5rem;border-bottom:1px solid var(--border);cursor:pointer;text-decoration:none;color:var(--t1);transition:background .15s}
    .mail-item:hover{background:var(--bg-hover)}
    .mail-item.unread{font-weight:600;background:var(--bg)}
    .mail-item.unread:hover{background:var(--bg-hover)}
    .mail-sender{font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding-right:1rem;color:var(--t1)}
    .mail-subject{font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--t2)}
    .mail-item.unread .mail-subject{color:var(--t1)}
    .mail-date{font-size:.75rem;color:var(--t3);text-align:right}
    .mail-avatar{width:28px;height:28px;border-radius:50%;background:var(--accent);color:white;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:bold;flex-shrink:0;}
    .mail-action-btn{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--t3);transition:background .2s,color .2s;font-size:1.2rem;cursor:pointer;}
    .mail-action-btn:hover{background:var(--bg3);color:var(--t1)}
    .list-header{padding:1rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;background:var(--bg2)}
    .list-title{font-size:1.1rem;font-weight:600}
    
    .toolbar{display:flex;gap:.5rem}
    .empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:var(--t3)}
    .empty-icon{font-size:3rem;margin-bottom:1rem;opacity:.5}

    /* Modal Styles */
    .modal-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);display:none;align-items:center;justify-content:center;z-index:1000}
    .modal-overlay.active{display:flex}
    .filter-modal{background:var(--bg2);padding:1.5rem;border-radius:12px;width:100%;max-width:500px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.5)}
    .modal-title{font-size:1.2rem;font-weight:600;margin-bottom:1rem}
    .modal-close{float:right;cursor:pointer;font-size:1.2rem;color:var(--t3)}
    .modal-close:hover{color:var(--t1)}

    /* ===== RESPONSIVE: Tablet ===== */
    @media(max-width:1024px){
        .mail-item{grid-template-columns:65px 160px 1fr 80px 40px;padding:.75rem 1rem}
    }

    /* ===== RESPONSIVE: Mobile ===== */
    @media(max-width:768px){
        .list-header{flex-direction:column;gap:.75rem;align-items:stretch;padding:1rem}
        .toolbar{flex-wrap:wrap;justify-content:flex-end}
        .toolbar .form-control{width:100% !important;min-width:0}
        .mail-item{grid-template-columns:65px 1fr auto 30px;padding:.75rem 1rem;gap:.25rem}
        .mail-sender{padding-right:.5rem;font-size:.85rem}
        .mail-subject{grid-column:2/3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.82rem;margin-top:2px}
        .mail-date{font-size:.7rem;grid-row:1;grid-column:3}
    }

    /* ===== RESPONSIVE: Small Phone ===== */
    @media(max-width:480px){
        .list-header{padding:.75rem}
        .list-title{font-size:.95rem}
        .mail-item{grid-template-columns:55px 1fr auto 25px;padding:.6rem .75rem}
        .mail-avatar{width:24px;height:24px;font-size:.6rem}
        .mail-sender{font-size:.8rem}
        .mail-subject{font-size:.78rem}
        .mail-action-btn{width:24px;height:24px;font-size:1rem}
    }
</style>
@endsection

@section('content')
    <form id="bulkForm" method="POST" action="{{ route('webmail.bulk') }}" style="display:flex;flex-direction:column;height:100%">
        @csrf
        <input type="hidden" name="folder" value="{{ $currentFolder ?? 'INBOX' }}">
        <div class="list-header">
            <div class="list-title">
                @if(isset($searchQuery))
                    Search: "{{ $searchQuery }}"
                @else
                    {{ isset($currentFolder) ? $currentFolder : 'Inbox' }}
                @endif
            </div>
            <div class="toolbar" style="align-items:center">
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;margin-right:0.5rem;" title="Select All">
                    <input type="checkbox" id="selectAllCheckbox" style="accent-color:var(--accent);width:16px;height:16px;cursor:pointer" onchange="toggleAllEmails(this.checked)">
                </label>
                <input type="text" name="q" form="searchForm" class="form-control" style="padding:.4rem;width:200px" placeholder="Search emails..." value="{{ $searchQuery ?? '' }}" onkeydown="if(event.key === 'Enter') { event.preventDefault(); document.getElementById('searchForm').submit(); }">
                <button type="submit" form="searchForm" class="btn btn-secondary" style="padding:.4rem .75rem">Search</button>

                @if(isset($currentFolder) && strcasecmp($currentFolder, 'Trash') === 0)
                    <button type="submit" form="emptyTrashForm" class="btn btn-secondary" style="color:var(--err)">Empty Trash</button>
                @endif
                <select name="action" class="form-control" style="width:auto;padding:.4rem;display:none" id="bulkActions" onchange="if(this.value) document.getElementById('bulkForm').submit()">
                    <option value="">Bulk Actions...</option>
                    <option value="read">Mark as Read</option>
                    <option value="unread">Mark as Unread</option>
                    <option value="move_junk">Move to Spam</option>
                    <option value="move_trash">Move to Trash</option>
                    <option value="delete">Delete Permanently</option>
                </select>
                <button type="button" class="btn-icon" title="Refresh" onclick="window.location.reload()">↻</button>
            </div>
        </div>
        
        @if(session('success'))
            <div style="padding:1rem 1.5rem 0"><div class="alert alert-success">{{ session('success') }}</div></div>
        @endif
        @if(session('error'))
            <div style="padding:1rem 1.5rem 0"><div class="alert alert-error">{{ session('error') }}</div></div>
        @endif
        @if($errors->any())
            <div style="padding:1rem 1.5rem 0"><div class="alert alert-error">{{ $errors->first() }}</div></div>
        @endif

        <div class="mail-list">
            @forelse($messages as $msg)
                <div class="mail-item {{ !$msg['seen'] ? 'unread' : '' }}" onclick="if(event.target.type !== 'checkbox' && !event.target.closest('.mail-action-btn')) window.location.href='{{ route('webmail.message.show', $msg['uid']) }}?folder={{ urlencode($currentFolder ?? 'INBOX') }}'">
                    <div style="display:flex;align-items:center;gap:.75rem">
                        <input type="checkbox" name="uids[]" value="{{ $msg['uid'] }}" class="mail-checkbox" style="accent-color:var(--accent);width:16px;height:16px;cursor:pointer">
                        <div class="mail-avatar">{{ strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $msg['from']), 0, 1) ?: '@') }}</div>
                    </div>
                    <div class="mail-sender" title="{{ $msg['from'] }}">{{ explode('<', $msg['from'])[0] }}</div>
                    <div class="mail-subject">{{ $msg['subject'] }}</div>
                    <div class="mail-date">{{ date('M d', strtotime($msg['date'])) }}</div>
                    <div class="mail-action-btn" title="Create Filter" onclick="event.stopPropagation(); openFilterModal('{{ htmlspecialchars($msg['from']) }}', '{{ htmlspecialchars($msg['subject']) }}')">⋮</div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3 style="font-weight:500;margin-bottom:.5rem">No messages found</h3>
                    <p style="font-size:.85rem">Your {{ isset($currentFolder) ? strtolower($currentFolder) : 'inbox' }} is empty.</p>
                </div>
            @endforelse
        </div>
    </form>
    
    @if(isset($currentFolder) && strcasecmp($currentFolder, 'Trash') === 0)
        <form id="emptyTrashForm" action="{{ route('webmail.trash.empty') }}" method="POST" style="display:none" onsubmit="return confirm('Are you sure you want to empty the Trash? This cannot be undone.')">
            @csrf
        </form>
    @endif
    
    <form id="searchForm" action="{{ route('webmail.search') }}" method="GET" style="display:none"></form>
    
    <!-- Quick Filter Modal -->
    <div class="modal-overlay" id="filterModal">
        <div class="filter-modal">
            <div class="modal-close" onclick="closeFilterModal()">×</div>
            <h3 class="modal-title">Create Mail Filter</h3>
            <p style="font-size:0.85rem;color:var(--t3);margin-bottom:1rem">Automatically process incoming emails.</p>
            
            <form action="{{ route('webmail.settings.filter.add') }}" method="POST">
                @csrf
                <div style="margin-bottom:1rem">
                    <label style="display:block;margin-bottom:0.3rem;font-size:0.85rem;color:var(--t2)">Filter Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Block Spam Sender" required>
                </div>
                
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
                    <div>
                        <label style="display:block;margin-bottom:0.3rem;font-size:0.85rem;color:var(--t2)">Match Field</label>
                        <select name="criteria_field" id="filterField" class="form-control" onchange="updateFilterValue()">
                            <option value="from">Sender Email (mail)</option>
                            <option value="subject">Subject Filter</option>
                            <option value="body">Has the word (Body keyword)</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:0.3rem;font-size:0.85rem;color:var(--t2)">Operator</label>
                        <select name="criteria_operator" class="form-control">
                            <option value="contains">Contains</option>
                            <option value="exact">Exact Match</option>
                        </select>
                    </div>
                </div>
                
                <div style="margin-bottom:1rem">
                    <label style="display:block;margin-bottom:0.3rem;font-size:0.85rem;color:var(--t2)">Keyword / Value</label>
                    <input type="text" name="criteria_value" id="filterValue" class="form-control" required>
                    <input type="hidden" id="rawFromValue">
                    <input type="hidden" id="rawSubjectValue">
                </div>
                
                <div style="margin-bottom:1.5rem">
                    <label style="display:block;margin-bottom:0.3rem;font-size:0.85rem;color:var(--t2)">Action</label>
                    <select name="action" class="form-control">
                        <option value="delete">Delete Permanently</option>
                        <option value="move_trash">Move to Trash</option>
                        <option value="move_spam">Move to Spam</option>
                    </select>
                </div>
                
                <div style="display:flex;justify-content:flex-end;gap:0.5rem">
                    <button type="button" class="btn btn-secondary" onclick="closeFilterModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Filter</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.querySelectorAll('.mail-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const anyChecked = document.querySelectorAll('.mail-checkbox:checked').length > 0;
                document.getElementById('bulkActions').style.display = anyChecked ? 'inline-block' : 'none';
                
                // Update 'Select All' checkbox state
                const allChecked = document.querySelectorAll('.mail-checkbox:checked').length === document.querySelectorAll('.mail-checkbox').length;
                document.getElementById('selectAllCheckbox').checked = allChecked;
            });
        });

        function toggleAllEmails(checked) {
            document.querySelectorAll('.mail-checkbox').forEach(cb => {
                cb.checked = checked;
            });
            const anyChecked = document.querySelectorAll('.mail-checkbox:checked').length > 0;
            document.getElementById('bulkActions').style.display = anyChecked ? 'inline-block' : 'none';
        }

        // Quick Filter Logic
        function openFilterModal(from, subject) {
            document.getElementById('filterModal').classList.add('active');
            
            // Extract pure email from "Name <email@domain.com>"
            let pureEmail = from;
            const match = from.match(/<([^>]+)>/);
            if(match) pureEmail = match[1];
            
            document.getElementById('rawFromValue').value = pureEmail;
            document.getElementById('rawSubjectValue').value = subject;
            
            // Trigger update to populate the value field based on default selection (Sender)
            updateFilterValue();
        }
        
        function closeFilterModal() {
            document.getElementById('filterModal').classList.remove('active');
        }
        
        function updateFilterValue() {
            const field = document.getElementById('filterField').value;
            const valInput = document.getElementById('filterValue');
            
            if (field === 'from') {
                valInput.value = document.getElementById('rawFromValue').value;
            } else if (field === 'subject') {
                valInput.value = document.getElementById('rawSubjectValue').value;
            } else {
                valInput.value = '';
                valInput.placeholder = 'Enter keyword...';
            }
        }
    </script>
</div>
@endsection
