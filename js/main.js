const authModal = document.getElementById('authModal');
const authBtn = document.getElementById('authBtn');
const closeAuth = document.getElementById('closeAuth');
const saveAuth = document.getElementById('saveAuth');
const authStatus = document.getElementById('authStatus');
const email = document.getElementById('email');
const apiKey = document.getElementById('apiKey');

function loadAuth() {
    const savedEmail = localStorage.getItem('cfEmail');
    const savedKey = localStorage.getItem('cfApiKey');
    if (savedEmail && savedKey) {
        email.value = savedEmail;
        apiKey.value = savedKey;
        authStatus.innerHTML = `<i class="fas fa-check-circle text-green-500 mr-1"></i>${savedEmail}`;
    }
}

function saveAuthInfo() {
    if (email.value && apiKey.value) {
        localStorage.setItem('cfEmail', email.value);
        localStorage.setItem('cfApiKey', apiKey.value);
        authStatus.innerHTML = `<i class="fas fa-check-circle text-green-500 mr-1"></i>${email.value}`;
        authModal.classList.add('hidden');
    } else {
        alert('请填写完整的认证信息');
    }
}

[
    [authBtn, () => authModal.classList.remove('hidden')],
    [closeAuth, () => authModal.classList.add('hidden')],
    [saveAuth, saveAuthInfo],
    [authModal, (e) => e.target === authModal && authModal.classList.add('hidden')]
].forEach(([el, handler]) => el.addEventListener('click', handler));

loadAuth();

async function handleAction(action) {
    const currentEmail = localStorage.getItem('cfEmail');
    const currentKey = localStorage.getItem('cfApiKey');
    
    if (!currentEmail || !currentKey) {
        alert('请先设置认证信息');
        authModal.classList.remove('hidden');
        return;
    }

    const [submitBtn, deleteBtn] = ['submit', 'delete'].map(id => document.getElementById(id));
    const result = document.getElementById('result');
    const output = document.getElementById('output');
    
    [submitBtn, deleteBtn].forEach(btn => {
        btn.disabled = true;
        btn.classList.add('opacity-50');
    });

    result.classList.remove('hidden');
    output.textContent = '处理中...';

    try {
        const response = await fetch('process.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action,
                email: currentEmail,
                apiKey: currentKey,
                domains: document.getElementById('domains').value,
                records: document.getElementById('records').value,
                httpsEnabled: document.getElementById('httpsToggle').checked
            })
        });

        output.textContent = await response.text();
    } catch (error) {
        output.textContent = '发生错误：' + error.message;
    } finally {
        [submitBtn, deleteBtn].forEach(btn => {
            btn.disabled = false;
            btn.classList.remove('opacity-50');
        });
    }
}

['submit', 'delete'].forEach(id => 
    document.getElementById(id).addEventListener('click', () => handleAction(id === 'submit' ? 'add' : 'delete'))
); 