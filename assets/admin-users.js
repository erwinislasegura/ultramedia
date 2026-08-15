(() => {
    const openModal = id => document.getElementById(id)?.classList.add('show');
    const closeModal = modal => modal?.classList.remove('show');
    const resetUserForm = () => {
        document.getElementById('userModalTitle').textContent = 'NUEVO USUARIO';
        document.getElementById('userId').value = '';
        document.getElementById('userName').value = '';
        document.getElementById('userEmail').value = '';
        document.getElementById('userStatus').value = 'active';
        document.getElementById('userPassword').value = '';
    };
    const resetRoleForm = () => {
        document.getElementById('roleModalTitle').textContent = 'NUEVO ROL';
        document.getElementById('roleId').value = '';
        document.getElementById('roleName').value = '';
        document.querySelectorAll('.role-permission').forEach(input => { input.checked = false; });
    };
    document.querySelectorAll('[data-create-user]').forEach(button => button.addEventListener('click', resetUserForm));
    document.querySelectorAll('[data-create-role]').forEach(button => button.addEventListener('click', resetRoleForm));
    document.querySelectorAll('[data-open]').forEach(button => button.addEventListener('click', () => openModal(button.dataset.open)));
    document.querySelectorAll('.modal-close').forEach(button => button.addEventListener('click', () => closeModal(button.closest('.admin-modal'))));
    document.querySelectorAll('.admin-modal').forEach(modal => modal.addEventListener('click', event => { if (event.target === modal) closeModal(modal); }));
    document.querySelectorAll('.edit-user').forEach(button => button.addEventListener('click', () => {
        const user = JSON.parse(button.dataset.user);
        document.getElementById('userModalTitle').textContent = 'EDITAR USUARIO';
        document.getElementById('userId').value = user.id;
        document.getElementById('userName').value = user.name;
        document.getElementById('userEmail').value = user.email;
        document.getElementById('userRole').value = user.role_id;
        document.getElementById('userStatus').value = user.status;
        document.getElementById('userPassword').value = '';
        openModal('userModal');
    }));
    document.querySelectorAll('.edit-role').forEach(button => button.addEventListener('click', () => {
        const role = JSON.parse(button.dataset.role);
        document.getElementById('roleModalTitle').textContent = 'EDITAR ROL';
        document.getElementById('roleId').value = role.id;
        document.getElementById('roleName').value = role.name;
        document.querySelectorAll('.role-permission').forEach(input => { input.checked = Array.isArray(role.permissions) && role.permissions.includes(input.value); });
        openModal('roleModal');
    }));
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeModal(document.querySelector('.admin-modal.show')); });
})();
