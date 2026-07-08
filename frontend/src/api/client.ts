export interface ApiResult<T> {
  code: number;
  message: string;
  data: T;
}

export interface Tenant {
  id: number;
  code: string;
  name: string;
  enabled: number;
  plan_code?: string | null;
}

export interface Application {
  id: number;
  code: string;
  name: string;
  entry_path: string;
  enabled: number;
}

export interface Plugin {
  id: number;
  code: string;
  name: string;
  version: string;
  enabled: number;
}

const token = () => localStorage.getItem('adminToken') || '';

export async function request<T>(url: string, options: RequestInit = {}): Promise<T> {
  const headers = new Headers(options.headers);
  headers.set('Content-Type', 'application/json');
  if (token()) headers.set('X-Admin-Token', token());

  const response = await fetch(url, { ...options, headers });
  const payload = await response.json() as ApiResult<T>;
  if (!response.ok || payload.code !== 0) {
    throw new Error(payload.message || '请求失败');
  }

  return payload.data;
}

export const api = {
  health: () => fetch('/health').then((response) => response.json() as Promise<{ status: string; tenant_code?: string }>),
  tenants: () => request<{ items: Tenant[] }>('/admin/tenants'),
  createTenant: (body: Partial<Tenant>) => request<{ id: number }>('/admin/tenants', { method: 'POST', body: JSON.stringify(body) }),
  deleteTenant: (id: number) => request(`/admin/tenants/${id}`, { method: 'DELETE' }),
  addDomain: (id: number, domain: string) => request<{ id: number }>(`/admin/tenants/${id}/domains`, { method: 'POST', body: JSON.stringify({ domain }) }),
  applications: () => request<{ items: Application[] }>('/admin/applications'),
  saveApplication: (body: Partial<Application>) => request('/admin/applications', { method: 'POST', body: JSON.stringify(body) }),
  plugins: () => request<{ items: Plugin[] }>('/admin/plugins'),
  savePlugin: (body: Partial<Plugin>) => request('/admin/plugins', { method: 'POST', body: JSON.stringify(body) }),
};
