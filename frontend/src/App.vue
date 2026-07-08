<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import * as echarts from 'echarts';
import { ElMessage, ElMessageBox } from 'element-plus';
import { api, type Application, type Plugin, type Tenant } from './api/client';

const adminToken = ref(localStorage.getItem('adminToken') || '');
const tenantCode = ref('-');
const tenants = ref<Tenant[]>([]);
const applications = ref<Application[]>([]);
const plugins = ref<Plugin[]>([]);
const chartEl = ref<HTMLDivElement>();

const tenantForm = reactive({ code: '', name: '', plan_code: '', enabled: 1 });
const appForm = reactive({ code: '', name: '', entry_path: '', enabled: 1 });
const pluginForm = reactive({ code: '', name: '', version: '0.1.0', enabled: 1 });
const shardForm = reactive({ table: 'orders', modulo: 16 });

const enabledTenants = computed(() => tenants.value.filter((item) => Number(item.enabled) === 1).length);
const shardSql = computed(() => {
  const width = Math.max(2, String(shardForm.modulo - 1).length);
  return Array.from({ length: shardForm.modulo }, (_, index) => {
    const suffix = String(index).padStart(width, '0');
    return `CREATE TABLE IF NOT EXISTS \`${shardForm.table}_${suffix}\` LIKE \`${shardForm.table}\`;`;
  }).join('\n');
});

function saveToken() {
  localStorage.setItem('adminToken', adminToken.value.trim());
  ElMessage.success('Token 已保存');
}

async function reload() {
  const [health, tenantData, appData, pluginData] = await Promise.all([
    api.health(),
    api.tenants(),
    api.applications(),
    api.plugins(),
  ]);
  tenantCode.value = health.tenant_code || '未识别';
  tenants.value = tenantData.items;
  applications.value = appData.items;
  plugins.value = pluginData.items;
  renderChart();
}

async function createTenant() {
  await api.createTenant(tenantForm);
  Object.assign(tenantForm, { code: '', name: '', plan_code: '', enabled: 1 });
  ElMessage.success('租户已创建');
  await reload();
}

async function deleteTenant(row: Tenant) {
  await ElMessageBox.confirm(`确定删除租户 ${row.name}？`, '删除确认', { type: 'warning' });
  await api.deleteTenant(row.id);
  ElMessage.success('租户已删除');
  await reload();
}

async function addDomain(row: Tenant) {
  const result = await ElMessageBox.prompt('请输入租户域名', `绑定域名：${row.name}`);
  await api.addDomain(row.id, result.value);
  ElMessage.success('域名已绑定');
}

async function saveApplication() {
  await api.saveApplication(appForm);
  Object.assign(appForm, { code: '', name: '', entry_path: '', enabled: 1 });
  ElMessage.success('应用已保存');
  await reload();
}

async function savePlugin() {
  await api.savePlugin(pluginForm);
  Object.assign(pluginForm, { code: '', name: '', version: '0.1.0', enabled: 1 });
  ElMessage.success('插件已保存');
  await reload();
}

function renderChart() {
  if (!chartEl.value) return;
  const chart = echarts.init(chartEl.value);
  chart.setOption({
    tooltip: {},
    grid: { left: 30, right: 20, top: 30, bottom: 30 },
    xAxis: { type: 'category', data: ['租户', '启用租户', '应用', '插件'] },
    yAxis: { type: 'value' },
    series: [{
      type: 'bar',
      data: [tenants.value.length, enabledTenants.value, applications.value.length, plugins.value.length],
      itemStyle: { color: '#4f46e5' },
    }],
  });
}

onMounted(() => {
  reload().catch((error) => ElMessage.error(error.message));
});
</script>

<template>
  <div class="min-h-screen bg-slate-100">
    <aside class="fixed inset-y-0 left-0 hidden w-64 bg-slate-950 p-6 text-white lg:block">
      <div class="mb-10 flex items-center gap-3">
        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-indigo-500 text-xl font-black">S</div>
        <div>
          <div class="text-lg font-bold">Webman SaaS</div>
          <div class="text-xs text-indigo-200">Vue 管理后台</div>
        </div>
      </div>
      <nav class="grid gap-2 text-sm text-slate-200">
        <a class="rounded-xl bg-white/10 px-4 py-3" href="#dashboard">概览</a>
        <a class="rounded-xl px-4 py-3 hover:bg-white/10" href="#tenants">租户</a>
        <a class="rounded-xl px-4 py-3 hover:bg-white/10" href="#extensions">应用与插件</a>
        <a class="rounded-xl px-4 py-3 hover:bg-white/10" href="#sharding">分表</a>
      </nav>
    </aside>

    <main class="p-5 lg:ml-64 lg:p-8">
      <section class="mb-6 flex flex-col justify-between gap-4 rounded-3xl bg-white p-6 shadow-sm lg:flex-row lg:items-center">
        <div>
          <h1 class="text-2xl font-black text-slate-950">SaaS 基础底座管理台</h1>
          <p class="mt-2 text-slate-500">租户、域名、应用、插件、Redis 与分表能力已接入，业务应用和插件可继续独立开发。</p>
        </div>
        <div class="flex gap-2">
          <el-input v-model="adminToken" show-password placeholder="X-Admin-Token" class="w-64" />
          <el-button type="primary" @click="saveToken">保存 Token</el-button>
          <el-button @click="reload">刷新</el-button>
        </div>
      </section>

      <section id="dashboard" class="mb-6 grid gap-4 md:grid-cols-4">
        <div class="rounded-3xl bg-gradient-to-br from-indigo-600 to-cyan-500 p-6 text-white shadow-sm">
          <div class="text-sm text-indigo-100">当前租户</div>
          <div class="mt-3 text-3xl font-black">{{ tenantCode }}</div>
        </div>
        <div class="rounded-3xl bg-white p-6 shadow-sm"><div class="text-sm text-slate-500">租户数量</div><div class="mt-3 text-3xl font-black">{{ tenants.length }}</div></div>
        <div class="rounded-3xl bg-white p-6 shadow-sm"><div class="text-sm text-slate-500">应用数量</div><div class="mt-3 text-3xl font-black">{{ applications.length }}</div></div>
        <div class="rounded-3xl bg-white p-6 shadow-sm"><div class="text-sm text-slate-500">插件数量</div><div class="mt-3 text-3xl font-black">{{ plugins.length }}</div></div>
      </section>

      <section class="mb-6 rounded-3xl bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-xl font-bold">运营统计</h2>
        <div ref="chartEl" class="h-72"></div>
      </section>

      <section id="tenants" class="mb-6 rounded-3xl bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
          <div><h2 class="text-xl font-bold">租户管理</h2><p class="text-slate-500">创建租户并绑定域名。</p></div>
        </div>
        <el-form :inline="true" :model="tenantForm" @submit.prevent="createTenant">
          <el-form-item><el-input v-model="tenantForm.code" placeholder="租户编码" /></el-form-item>
          <el-form-item><el-input v-model="tenantForm.name" placeholder="租户名称" /></el-form-item>
          <el-form-item><el-input v-model="tenantForm.plan_code" placeholder="套餐编码" /></el-form-item>
          <el-form-item><el-switch v-model="tenantForm.enabled" :active-value="1" :inactive-value="0" active-text="启用" /></el-form-item>
          <el-form-item><el-button type="primary" native-type="submit">创建租户</el-button></el-form-item>
        </el-form>
        <el-table :data="tenants" stripe>
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="code" label="编码" />
          <el-table-column prop="name" label="名称" />
          <el-table-column prop="plan_code" label="套餐" />
          <el-table-column label="状态"><template #default="{ row }"><el-tag :type="Number(row.enabled) ? 'success' : 'danger'">{{ Number(row.enabled) ? '启用' : '禁用' }}</el-tag></template></el-table-column>
          <el-table-column label="操作" width="220"><template #default="{ row }"><el-button size="small" @click="addDomain(row)">绑定域名</el-button><el-button size="small" type="danger" @click="deleteTenant(row)">删除</el-button></template></el-table-column>
        </el-table>
      </section>

      <section id="extensions" class="mb-6 grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl bg-white p-6 shadow-sm">
          <h2 class="mb-4 text-xl font-bold">应用</h2>
          <el-form :model="appForm" label-position="top" @submit.prevent="saveApplication">
            <el-form-item label="编码"><el-input v-model="appForm.code" /></el-form-item>
            <el-form-item label="名称"><el-input v-model="appForm.name" /></el-form-item>
            <el-form-item label="入口路径"><el-input v-model="appForm.entry_path" /></el-form-item>
            <el-button type="primary" native-type="submit">保存应用</el-button>
          </el-form>
          <el-table :data="applications" class="mt-4" size="small"><el-table-column prop="code" label="编码" /><el-table-column prop="name" label="名称" /><el-table-column prop="entry_path" label="入口" /></el-table>
        </div>
        <div class="rounded-3xl bg-white p-6 shadow-sm">
          <h2 class="mb-4 text-xl font-bold">插件</h2>
          <el-form :model="pluginForm" label-position="top" @submit.prevent="savePlugin">
            <el-form-item label="编码"><el-input v-model="pluginForm.code" /></el-form-item>
            <el-form-item label="名称"><el-input v-model="pluginForm.name" /></el-form-item>
            <el-form-item label="版本"><el-input v-model="pluginForm.version" /></el-form-item>
            <el-button type="primary" native-type="submit">保存插件</el-button>
          </el-form>
          <el-table :data="plugins" class="mt-4" size="small"><el-table-column prop="code" label="编码" /><el-table-column prop="name" label="名称" /><el-table-column prop="version" label="版本" /></el-table>
        </div>
      </section>

      <section id="sharding" class="rounded-3xl bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-xl font-bold">分表 SQL 预览</h2>
        <div class="mb-4 flex gap-3">
          <el-input v-model="shardForm.table" placeholder="基础表名" class="max-w-xs" />
          <el-input-number v-model="shardForm.modulo" :min="1" />
        </div>
        <pre class="overflow-auto rounded-2xl bg-slate-950 p-4 text-sm text-indigo-100">{{ shardSql }}</pre>
      </section>
    </main>
  </div>
</template>
