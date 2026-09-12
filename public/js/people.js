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
    peopleBody.innerHTML = '<tr><td colspan="7" style="padding:26px;text-align:center;color:var(--muted)">Loading team directory from database…</td></tr>';
    return;
  }

  peopleBody.innerHTML = list.map((u, i) => {
    const pillClass = JMOS_STATE.accessPill[u.role] || '';
    const pill = pillClass 
      ? `<span class="pill ${pillClass}">${u.role[0].toUpperCase() + u.role.slice(1)}</span>`
      : `<span class="pill" style="background:var(--paper);border:1px solid var(--line);color:var(--muted)">Team</span>`;

    const removeBtn = isOwner 
      ? `<button class="iconact danger" data-remove-user-id="${u.id || i}" title="Remove user">
           <svg viewBox="0 0 24 24"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg>
         </button>` 
      : '';

    return `<tr>
      <td>
        <div class="cn">
          <span class="lg" style="background:${u.color}">${escHtml(u.ini)}</span>
          ${escHtml(u.name)}
        </div>
      </td>
      <td>${escHtml(u.title || 'Team')}</td>
      <td>${pill}</td>
      <td>${escHtml(u.type || 'Full-time')}</td>
      <td class="mono">${escHtml(u.pay || '—')}</td>
      <td class="mono" style="color:var(--faint);font-size:11px">${escHtml(u.email)}</td>
      <td><div class="rowact">${removeBtn}</div></td>
    </tr>`;
  }).join('');
}

async function ensurePeople() {
  try {
    const users = await JMOS_API.get('/users');
    if (Array.isArray(users)) {
      JMOS_STATE.users = users.map((u, i) => ({
        id: u.id,
        name: u.name,
        title: u.title || 'Team',
        email: u.email,
        role: u.role || 'team',
        type: u.type || 'Full-time',
        pay: u.pay || '—',
        color: u.color || JMOS_COLORS[i % JMOS_COLORS.length],
        ini: u.initials || getInitials(u.name)
      }));
      renderPeople();
      renderDemoAccounts();
    }
  } catch (err) {
    console.error('Error fetching users:', err);
  }
}

function initPeople() {
  const addUserBtn = document.getElementById('addUserBtn');
  const saveUserBtn = document.getElementById('saveUserBtn');
  const confirmYes = document.getElementById('confirmYes');

  if (addUserBtn) {
    addUserBtn.onclick = () => {
      ['nuName', 'nuTitle', 'nuEmail', 'nuPay'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
      });
      const nuPass = document.getElementById('nuPass');
      if (nuPass) nuPass.value = 'jeota2024';
      openModal('userModal');
    };
  }

  if (saveUserBtn) {
    saveUserBtn.onclick = async () => {
      const name = document.getElementById('nuName').value.trim();
      const email = document.getElementById('nuEmail').value.trim();

      if (!name || !email) {
        showToast('Add a name and email', 'Both are needed to create a login', true);
        return;
      }

      saveUserBtn.disabled = true;
      saveUserBtn.textContent = 'Adding…';

      try {
        const res = await JMOS_API.post('/users', {
          name,
          title: document.getElementById('nuTitle').value.trim() || 'Team',
          email,
          role: document.getElementById('nuRole').value,
          type: document.getElementById('nuType').value,
          pay: document.getElementById('nuPay').value.trim() || '—',
          password: document.getElementById('nuPass').value || 'jeota2024',
        });

        closeModal('userModal');
        showToast(name + ' added', email + ' can now sign in');
        await ensurePeople();
      } catch (err) {
        showToast('Failed to add person', err.message, true);
      } finally {
        saveUserBtn.disabled = false;
        saveUserBtn.textContent = 'Add person & create login';
      }
    };
  }

  // Remove person trigger
  document.addEventListener('click', (e) => {
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
