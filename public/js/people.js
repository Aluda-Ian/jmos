/* ==========================================================================
   JMOS — Team Members & Permissions Management (Live Database)
   ========================================================================== */

let pendingUserRemoval = null;

function renderPeople() {
  const peopleBody = document.getElementById('peopleBody');
  const addUserBtn = document.getElementById('addUserBtn');
  if (!peopleBody) return;

  const isOwner = JMOS_STATE.currentUser && JMOS_STATE.currentUser.role === 'owner';
  if (addUserBtn) addUserBtn.style.display = isOwner ? '' : 'none';

  const list = JMOS_STATE.users || [];
  if (!list.length) {
    peopleBody.innerHTML = '<tr><td colspan="8" style="padding:26px;text-align:center;color:var(--muted)">No team members found in database.</td></tr>';
    return;
  }

  peopleBody.innerHTML = list.map((u, i) => {
    const roleSlug = (u.role || 'team').toLowerCase();
    const pillClass = (JMOS_STATE.accessPill && JMOS_STATE.accessPill[roleSlug]) || '';
    const roleLabel = (u.role && typeof u.role === 'string') ? (u.role.charAt(0).toUpperCase() + u.role.slice(1)) : 'Team';
    const pill = pillClass 
      ? `<span class="pill ${pillClass}">${escHtml(roleLabel)}</span>`
      : `<span class="pill" style="background:var(--paper);border:1px solid var(--line);color:var(--muted)">${escHtml(roleLabel)}</span>`;

    const avatarHtml = u.avatar_url 
      ? `<div style="width:32px;height:32px;border-radius:50%;overflow:hidden;border:1px solid var(--line);flex-shrink:0"><img src="${escHtml(u.avatar_url)}" alt="${escHtml(u.name)}" style="width:100%;height:100%;object-fit:cover;display:block"></div>`
      : `<span class="lg" style="background:${u.color || '#C52523'};width:32px;height:32px;font-size:12px">${escHtml(u.ini || getInitials(u.name))}</span>`;

    const editBtn = isOwner 
      ? `<button class="iconact" data-edit-user-id="${u.id || i}" title="Edit team member, salary, department & role">
           <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
         </button>` 
      : '';

    const removeBtn = isOwner 
      ? `<button class="iconact danger" data-remove-user-id="${u.id || i}" title="Remove user">
           <svg viewBox="0 0 24 24"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg>
         </button>` 
      : '';

    return `<tr>
      <td>
        <div class="cn" style="gap:10px">
          ${avatarHtml}
          <div>
            <div style="font-weight:600;color:var(--ink)">${escHtml(u.name)}</div>
            ${u.phone ? `<div style="font-size:11px;color:var(--muted)">${escHtml(u.phone)}</div>` : ''}
          </div>
        </div>
      </td>
      <td>${escHtml(u.title || 'Team')}</td>
      <td><span class="pill" style="background:var(--paper);border:1px solid var(--line);font-size:11px;color:var(--ink)">${escHtml(u.department || '—')}</span></td>
      <td>${pill}</td>
      <td>${escHtml(u.type || 'Full-time')}</td>
      <td class="mono" style="font-weight:600;color:var(--ink)">${escHtml(u.pay || '—')}</td>
      <td class="mono" style="color:var(--faint);font-size:11px">
        <div>${escHtml(u.email)}</div>
        ${u.secondary_email ? `<div style="font-size:10px;color:var(--muted)" title="Secondary alert email">↳ ${escHtml(u.secondary_email)}</div>` : ''}
      </td>
      <td><div class="rowact" style="justify-content:flex-end">${editBtn}${removeBtn}</div></td>
    </tr>`;
  }).join('');
}

async function ensurePeople() {
  try {
    const res = await JMOS_API.get('/users');
    const users = Array.isArray(res) ? res : (res && Array.isArray(res.data) ? res.data : []);
    if (Array.isArray(users)) {
      JMOS_STATE.users = users.map((u, i) => ({
        id: u.id,
        name: u.name,
        title: u.title || 'Team',
        department: u.department || '',
        email: u.email,
        phone: u.phone || '',
        secondary_email: u.secondary_email || null,
        avatar_url: u.avatar_url || null,
        bio: u.bio || '',
        role: u.role || 'team',
        type: u.type || 'Full-time',
        pay: u.pay || '—',
        color: u.color || (typeof JMOS_COLORS !== 'undefined' ? JMOS_COLORS[i % JMOS_COLORS.length] : '#C52523'),
        ini: u.initials || (typeof getInitials === 'function' ? getInitials(u.name) : 'TM')
      }));

      // If current user is in list, sync local state
      if (JMOS_STATE.currentUser) {
        const matchingCurrent = JMOS_STATE.users.find(x => x.id === JMOS_STATE.currentUser.id || x.email === JMOS_STATE.currentUser.email);
        if (matchingCurrent) {
          Object.assign(JMOS_STATE.currentUser, matchingCurrent);
          try {
            localStorage.setItem('jmos_user', JSON.stringify(JMOS_STATE.currentUser));
          } catch (_) {}
          if (typeof applyAuthenticatedUI === 'function') {
            applyAuthenticatedUI(JMOS_STATE.currentUser);
          }
        }
      }

      renderPeople();
      renderDemoAccounts();
      if (typeof updateUserRoleDropdowns === 'function') {
        updateUserRoleDropdowns();
      }
    }
  } catch (err) {
    console.error('Error fetching users:', err);
  }
}

function openEditUserModal(user) {
  if (typeof updateUserRoleDropdowns === 'function') {
    updateUserRoleDropdowns();
  }

  const editId = document.getElementById('editUserId');
  if (editId) editId.value = user.id;

  const mTitle = document.getElementById('userModalTitle');
  if (mTitle) mTitle.textContent = 'Edit Team Member';

  const mSub = document.getElementById('userModalSub');
  if (mSub) mSub.textContent = 'Update member profile, role, department, salary & wages.';

  const saveBtn = document.getElementById('saveUserBtn');
  if (saveBtn) saveBtn.textContent = 'Update team member';

  const nuPassLabel = document.getElementById('nuPassLabel');
  if (nuPassLabel) nuPassLabel.textContent = 'New password (leave blank to keep)';

  const nuName = document.getElementById('nuName');
  if (nuName) nuName.value = user.name || '';

  const nuTitle = document.getElementById('nuTitle');
  if (nuTitle) nuTitle.value = user.title || '';

  const nuDept = document.getElementById('nuDept');
  if (nuDept) nuDept.value = user.department || '';

  const nuPhone = document.getElementById('nuPhone');
  if (nuPhone) nuPhone.value = user.phone || '';

  const nuEmail = document.getElementById('nuEmail');
  if (nuEmail) nuEmail.value = user.email || '';

  const nuRole = document.getElementById('nuRole');
  if (nuRole) nuRole.value = user.role || 'team';

  const nuType = document.getElementById('nuType');
  if (nuType) nuType.value = user.type || 'Full-time';

  const nuPay = document.getElementById('nuPay');
  if (nuPay) nuPay.value = user.pay || '';

  const nuPass = document.getElementById('nuPass');
  if (nuPass) nuPass.value = '';

  // Avatar preview in modal
  const avBox = document.getElementById('nuAvatarBox');
  const avFileName = document.getElementById('nuAvatarFileName');
  const avFile = document.getElementById('nuAvatarFile');
  if (avFile) avFile.value = '';
  if (avFileName) avFileName.textContent = user.avatar_url ? 'Current photo set' : 'No file chosen';
  if (avBox) {
    if (user.avatar_url) {
      avBox.innerHTML = `<img src="${escHtml(user.avatar_url)}" style="width:100%;height:100%;object-fit:cover;display:block">`;
    } else {
      avBox.innerHTML = `<span id="nuAvatarInitials">${escHtml(user.ini || getInitials(user.name))}</span>`;
      avBox.style.background = user.color || 'var(--red)';
    }
  }

  openModal('userModal');
}

function initPeople() {
  const addUserBtn = document.getElementById('addUserBtn');
  const saveUserBtn = document.getElementById('saveUserBtn');
  const confirmYes = document.getElementById('confirmYes');
  const nuAvatarFile = document.getElementById('nuAvatarFile');

  // Preview chosen avatar inside modal
  if (nuAvatarFile) {
    nuAvatarFile.addEventListener('change', (e) => {
      const file = e.target.files && e.target.files[0];
      const avBox = document.getElementById('nuAvatarBox');
      const avFileName = document.getElementById('nuAvatarFileName');
      if (file) {
        if (avFileName) avFileName.textContent = file.name;
        if (avBox) {
          const reader = new FileReader();
          reader.onload = (ev) => {
            avBox.innerHTML = `<img src="${ev.target.result}" style="width:100%;height:100%;object-fit:cover;display:block">`;
          };
          reader.readAsDataURL(file);
        }
      }
    });
  }

  if (addUserBtn) {
    addUserBtn.onclick = () => {
      const editId = document.getElementById('editUserId');
      if (editId) editId.value = '';

      const mTitle = document.getElementById('userModalTitle');
      if (mTitle) mTitle.textContent = 'Add a person';

      const mSub = document.getElementById('userModalSub');
      if (mSub) mSub.textContent = "They'll get their own login and only see what their access level allows.";

      const saveBtn = document.getElementById('saveUserBtn');
      if (saveBtn) saveBtn.textContent = 'Add person & create login';

      const nuPassLabel = document.getElementById('nuPassLabel');
      if (nuPassLabel) nuPassLabel.textContent = 'Temporary password';

      ['nuName', 'nuTitle', 'nuDept', 'nuPhone', 'nuEmail', 'nuPay'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
      });

      const nuPass = document.getElementById('nuPass');
      if (nuPass) nuPass.value = 'jeota2024';

      const avFile = document.getElementById('nuAvatarFile');
      if (avFile) avFile.value = '';
      const avFileName = document.getElementById('nuAvatarFileName');
      if (avFileName) avFileName.textContent = 'No file chosen';
      const avBox = document.getElementById('nuAvatarBox');
      if (avBox) {
        avBox.innerHTML = '<span id="nuAvatarInitials">TM</span>';
        avBox.style.background = 'var(--red)';
      }

      openModal('userModal');
    };
  }

  if (saveUserBtn) {
    saveUserBtn.onclick = async () => {
      const editIdEl = document.getElementById('editUserId');
      const editUserId = editIdEl ? editIdEl.value.trim() : '';
      const isEdit = Boolean(editUserId);

      const name = document.getElementById('nuName').value.trim();
      const email = document.getElementById('nuEmail').value.trim();
      const title = document.getElementById('nuTitle').value.trim() || 'Team';
      const department = document.getElementById('nuDept').value.trim() || 'Production';
      const phone = document.getElementById('nuPhone').value.trim();
      const role = document.getElementById('nuRole').value;
      const type = document.getElementById('nuType').value;
      const pay = document.getElementById('nuPay').value.trim() || '—';
      const password = document.getElementById('nuPass').value;
      const avatarInput = document.getElementById('nuAvatarFile');
      const avatarFile = avatarInput && avatarInput.files && avatarInput.files[0];

      if (!name || !email) {
        showToast('Add a name and email', 'Both are needed for team login & identification', true);
        return;
      }

      saveUserBtn.disabled = true;
      saveUserBtn.textContent = isEdit ? 'Updating…' : 'Adding…';

      const sendInviteEl = document.getElementById('nuSendInviteEmail');
      const sendInvite = sendInviteEl ? sendInviteEl.checked : true;

      try {
        if (avatarFile) {
          // Send via FormData to handle file upload
          const formData = new FormData();
          formData.append('name', name);
          formData.append('email', email);
          formData.append('title', title);
          formData.append('department', department);
          formData.append('phone', phone);
          formData.append('role', role);
          formData.append('type', type);
          formData.append('pay', pay);
          formData.append('send_invite_email', sendInvite ? '1' : '0');
          if (password) formData.append('password', password);
          formData.append('avatar', avatarFile);

          if (isEdit) {
            await JMOS_API.upload('/users/' + editUserId, formData);
          } else {
            await JMOS_API.upload('/users', formData);
          }
        } else {
          // Send as JSON payload
          const payload = {
            name,
            email,
            title,
            department,
            phone,
            role,
            type,
            pay,
            send_invite_email: sendInvite
          };
          if (password) payload.password = password;

          if (isEdit) {
            await JMOS_API.put('/users/' + editUserId, payload);
          } else {
            payload.password = password || 'jeota2024';
            await JMOS_API.post('/users', payload);
          }
        }

        closeModal('userModal');
        showToast(
          isEdit ? 'Team member updated' : name + ' added',
          isEdit ? 'Details, department, salary and role saved.' : (sendInvite ? `Invitation & password setup email sent to ${email}` : `${email} can now sign in.`)
        );
        await ensurePeople();
      } catch (err) {
        showToast(isEdit ? 'Failed to update member' : 'Failed to add person', err.message, true);
      } finally {
        saveUserBtn.disabled = false;
        saveUserBtn.textContent = isEdit ? 'Update team member' : 'Add person & create login';
      }
    };
  }

  // Edit or Remove person triggers via event delegation
  document.addEventListener('click', (e) => {
    // Edit trigger
    const editBtn = e.target.closest('[data-edit-user-id]');
    if (editBtn) {
      const id = editBtn.getAttribute('data-edit-user-id');
      const user = JMOS_STATE.users.find(u => String(u.id) === String(id)) || JMOS_STATE.users[id];
      if (user) {
        openEditUserModal(user);
      }
      return;
    }

    // Remove trigger
    const removeBtn = e.target.closest('[data-remove-user-id]');
    if (!removeBtn) return;

    const id = removeBtn.getAttribute('data-remove-user-id');
    const user = JMOS_STATE.users.find(u => String(u.id) === String(id)) || JMOS_STATE.users[id];
    if (!user) return;

    if (JMOS_STATE.currentUser && user.email === JMOS_STATE.currentUser.email) {
      showToast("You can't remove yourself", 'Ask another owner to do it', true);
      return;
    }

    if (user.role === 'owner' && JMOS_STATE.users.filter(x => x.role === 'owner').length <= 1) {
      showToast("Can't remove the last owner", 'Promote someone first', true);
      return;
    }

    pendingUserRemoval = user;
    const confirmMsg = document.getElementById('confirmMsg');
    if (confirmMsg) confirmMsg.textContent = `Remove ${user.name}? They will immediately lose access to JMOS.`;
    openModal('confirmModal');
  });

  if (confirmYes) {
    confirmYes.onclick = async () => {
      if (pendingUserRemoval) {
        const user = pendingUserRemoval;
        confirmYes.disabled = true;
        confirmYes.textContent = 'Removing…';

        try {
          if (user.id) {
            await JMOS_API.delete('/users/' + user.id);
          }
          pendingUserRemoval = null;
          closeModal('confirmModal');
          showToast(user.name + ' removed', 'Account deleted from database', true);
          await ensurePeople();
        } catch (err) {
          showToast('Failed to remove user', err.message, true);
        } finally {
          confirmYes.disabled = false;
          confirmYes.textContent = 'Remove & delete';
        }
      }
    };
  }
}
