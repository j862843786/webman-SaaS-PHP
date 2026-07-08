const state = {
  tenants: [],
  applications: [],
  plugins: [],
  token: localStorage.getItem('adminToken') || '',
};

const $ = (selector) => document.querySelector(selector);
const toast = (message) => {
  const el = $('#toast');
  el.textContent = message;
  el.hidden = false;
  setTimeout(() => { el.hidden = true; }, 2400);
};

const headers = () => {
  const value = { 'Content-Type': 'application/json' };
  if (state.token) value['X-Admin-Token'] = state.token;
  return value;
};

async function api(path, options = {}) {
  const response = await fetch(path, { headers: headers(), ...options });
  const payload = await response.json().catch(() => ({ code: response.status, message: response.statusText, data: {} }));
  if (!response.ok || payload.code) {
    throw new Error(payload.message || '请求失败');
  }
  return payload.data || {};
}

function bindNavigation() {
  document.querySelectorAll('nav a').forEach((link) => {
    link.addEventListener('click', () => {
      document.querySelectorAll('nav a').forEach((item) => item.classList.remove('active'));
      link.classList.add('active');
    });
  });
}

async function loadHealth() {
  const response = await fetch('/health');
  const data = await response.json();
  $('#tenant-code').textContent = data.tenant_code || '未识别';
}

async function loadTenants() {
  const data = await api('/admin/tenants');
  state.tenants = data.items || [];
  $('#tenant-count').textContent = state.tenants.length;
  $('#tenant-table').innerHTML = state.tenants.map((tenant) => `
    <tr>
      <td>${tenant.id}</td>
      <td>${tenant.code}</td>
      <td>${tenant.name}</td>
      <td>${tenant.plan_code || '-'}</td>
      <td><span class="badge ${Number(tenant.enabled) ? '' : 'off'}">${Number(tenant.enabled) ? '启用' : '禁用'}</span></td>
      <td><button data-domain="${tenant.id}" class="ghost">绑定域名</button></td>
      <td><button data-delete="${tenant.id}" class="danger">删除</button></td>
    </tr>`).join('');
}

async function loadApplications() {
  const data = await api('/admin/applications');
  state.applications = data.items || [];
  $('#app-count').textContent = state.applications.length;
  $('#app-list').innerHTML = state.applications.map((app) => `
    <li><span><strong>${app.name}</strong><br><small>${app.code} · ${app.entry_path}</small></span><span class="badge ${Number(app.enabled) ? '' : 'off'}">${Number(app.enabled) ? '启用' : '禁用'}</span></li>`).join('');
}

async function loadPlugins() {
  const data = await api('/admin/plugins');
  state.plugins = data.items || [];
  $('#plugin-count').textContent = state.plugins.length;
  $('#plugin-list').innerHTML = state.plugins.map((plugin) => `
    <li><span><strong>${plugin.name}</strong><br><small>${plugin.code} · v${plugin.version}</small></span><span class="badge ${Number(plugin.enabled) ? '' : 'off'}">${Number(plugin.enabled) ? '启用' : '禁用'}</span></li>`).join('');
}

function formData(form) {
  return Object.fromEntries(new FormData(form).entries());
}

function bindForms() {
  $('#admin-token').value = state.token;
  $('#save-token').addEventListener('click', () => {
    state.token = $('#admin-token').value.trim();
    localStorage.setItem('adminToken', state.token);
    toast('Token 已保存');
  });

  $('#tenant-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    await api('/admin/tenants', { method: 'POST', body: JSON.stringify(formData(event.target)) });
    event.target.reset();
    toast('租户已创建');
    await loadTenants();
  });

  $('#tenant-table').addEventListener('click', async (event) => {
    const deleteId = event.target.dataset.delete;
    const domainId = event.target.dataset.domain;
    if (deleteId && confirm('确定删除该租户？')) {
      await api(`/admin/tenants/${deleteId}`, { method: 'DELETE' });
      toast('租户已删除');
      await loadTenants();
    }
    if (domainId) {
      const domain = prompt('请输入绑定域名，例如 acme.example.com');
      if (domain) {
        await api(`/admin/tenants/${domainId}/domains`, { method: 'POST', body: JSON.stringify({ domain }) });
        toast('域名已绑定');
      }
    }
  });

  $('#app-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    await api('/admin/applications', { method: 'POST', body: JSON.stringify(formData(event.target)) });
    event.target.reset();
    toast('应用已保存');
    await loadApplications();
  });

  $('#plugin-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    await api('/admin/plugins', { method: 'POST', body: JSON.stringify(formData(event.target)) });
    event.target.reset();
    toast('插件已保存');
    await loadPlugins();
  });

  $('#reload-tenants').addEventListener('click', loadTenants);
  $('#reload-apps').addEventListener('click', loadApplications);
  $('#reload-plugins').addEventListener('click', loadPlugins);
  $('#preview-shards').addEventListener('click', () => {
    const table = $('#shard-table').value || 'orders';
    const modulo = Number($('#shard-modulo').value || 16);
    const width = Math.max(2, String(modulo - 1).length);
    $('#shard-preview').textContent = Array.from({ length: modulo }, (_, i) => {
      const suffix = String(i).padStart(width, '0');
      return `CREATE TABLE IF NOT EXISTS \`${table}_${suffix}\` LIKE \`${table}\`;`;
    }).join('\n');
  });
}

async function bootstrap() {
  bindNavigation();
  bindForms();
  await loadHealth().catch(() => { $('#tenant-code').textContent = '未连接'; });
  await Promise.all([
    loadTenants().catch((error) => toast(error.message)),
    loadApplications().catch((error) => toast(error.message)),
    loadPlugins().catch((error) => toast(error.message)),
  ]);
}

bootstrap();
