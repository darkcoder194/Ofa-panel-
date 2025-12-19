<template>
  <div class="ofa-theme-page">
    <h1>OFA Theme Manager</h1>

    <div class="palettes">
      <div v-for="p in palettes" :key="p.id" class="palette">
        <div class="preview" :style="previewStyle(p)"></div>
        <div class="meta">
          <strong>{{ p.name }}</strong>
          <div>
            <button @click="setDefault(p)">Set Default</button>
            <button @click="previewPalette(p)">Preview</button>
            <button @click="editPalette(p)">Edit</button>
            <button @click="exportPalette(p)">Export</button>
            <button @click="deletePalette(p)">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <div class="controls">
      <button @click="clearPreview">Clear Preview</button>
      <button @click="openImport">Import Palette</button>
      <input ref="importFile" type="file" style="display:none" @change="handleFile" accept="application/json,.json" />
    </div>

    <div class="branding-link" style="margin-top:12px">
      <h3>Branding</h3>
      <div>
        <a href="#" @click.prevent="openBranding">Open Branding Controls</a>
      </div>
    </div>

    <div class="create">
      <h2>Create Palette</h2>
      <input v-model="newPalette.name" placeholder="Name" />
      <input v-model="newPalette.slug" placeholder="slug" />
      <div class="colors">
        <div v-for="(val, key) in newPalette.colors" :key="key" class="color-row">
          <label>{{ key }}</label>
          <input type="color" v-model="newPalette.colors[key]" />
        </div>
      </div>
      <button @click="createPalette">Create</button>
    </div>

    <div class="server-tools" style="margin-top:20px; border-top:1px solid #e5e7eb; padding-top:12px">
      <h3>Server Tools</h3>
      <div>
        <label>Server UUID</label>
        <input v-model="serverUuid" placeholder="server-uuid" />
      </div>
      <div>
        <label>Version</label>
        <input v-model="serverVersion" placeholder="1.18.1" />
        <button @click="requestVersionChange">Request Version Change</button>
      </div>
      <div>
        <label>Egg</label>
        <input v-model="serverEgg" placeholder="egg-id or slug" />
        <button @click="requestEggChange">Request Egg Change</button>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="editingPalette" class="edit-modal">
      <div class="modal-content">
        <h3>Edit Palette</h3>
        <input v-model="editingPalette.name" placeholder="Name" />
        <div class="colors">
          <div v-for="(val, key) in editingPalette.colors" :key="key" class="color-row">
            <label>{{ key }}</label>
            <input type="color" v-model="editingPalette.colors[key]" />
          </div>
        </div>
        <div class="modal-actions">
          <button @click="saveEdit">Save</button>
          <button @click="cancelEdit">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ColorPicker from './components/ColorPicker.vue';

export default {
  components: { ColorPicker },
  data() {
    return {
      palettes: [],
      newPalette: {
        name: '',
        slug: '',
        colors: {
          primary: '#1f2937',
          accent: '#4f46e5',
          background: '#ffffff',
          text: '#111827',
        },
      },
      editingPalette: null,
      // server tools
      serverUuid: '',
      serverVersion: '',
      serverEgg: '',
    };
  },
  created() {
    this.fetchPalettes();

    // Listen for real-time preview broadcasts if Echo is configured
    try {
      if (window.Echo) {
        window.Echo.private('ofa.admin').listen('PalettePreviewed', (e) => {
          if (e && e.colors) {
            Object.keys(e.colors).forEach(k => document.documentElement.style.setProperty('--ofa-'+k, e.colors[k]));
          }
        });
      }
    } catch (err) {
      // silent fail if Echo not configured
      // console.warn('Echo not configured for OFA real-time preview');
    }
  },
  methods: {
    csrfToken() {
      const el = document.querySelector('meta[name="csrf-token"]');
      return el ? el.getAttribute('content') : '';
    },

    async fetchPalettes() {
      const res = await fetch('/admin/ofa/themes');
      this.palettes = await res.json();
    },
    previewStyle(p) {
      const primary = p.colors?.primary || '#1f2937';
      const background = p.colors?.background || '#ffffff';
      return { background, border: `3px solid ${primary}` };
    },
    async createPalette() {
      const res = await fetch('/admin/ofa/themes', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken() },
        body: JSON.stringify(this.newPalette),
      });
      if (res.ok) {
        this.newPalette.name = '';
        this.newPalette.slug = '';
        await this.fetchPalettes();
      }
    },
    async setDefault(p) {
      const res = await fetch(`/admin/ofa/themes/${p.id}/default`, { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrfToken() } });
      if (res.ok) await this.fetchPalettes();
    },
    async previewPalette(p) {
      const res = await fetch(`/admin/ofa/themes/${p.id}/preview`, { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrfToken() } });
      if (res.ok) {
        // Apply preview immediately using returned palette colors
        const pal = await res.json();
        Object.keys(pal.colors || {}).forEach(k => document.documentElement.style.setProperty('--ofa-'+k, pal.colors[k]));
      }
    },
    async clearPreview() {
      const res = await fetch('/admin/ofa/preview/clear', { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrfToken() } });
      if (res.ok) {
        // Reload palettes to get the current default back
        await this.fetchPalettes();
        const def = this.palettes.find(p => p.is_default) || this.palettes[0];
        if (def) Object.keys(def.colors || {}).forEach(k => document.documentElement.style.setProperty('--ofa-'+k, def.colors[k]));
      }
    },

    async requestVersionChange() {
      if (!this.serverUuid || !this.serverVersion) return alert('Please fill server UUID and version');
      const res = await fetch('/admin/ofa/servers/request-change', {
        method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken() },
        body: JSON.stringify({ server_uuid: this.serverUuid, type: 'version', payload: { version: this.serverVersion } })
      });
      if (res.ok) { alert('Change requested'); this.serverVersion = ''; }
      else alert('Request failed');
    },

    async requestEggChange() {
      if (!this.serverUuid || !this.serverEgg) return alert('Please fill server UUID and egg');
      const res = await fetch('/admin/ofa/servers/request-change', {
        method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken() },
        body: JSON.stringify({ server_uuid: this.serverUuid, type: 'egg', payload: { egg: this.serverEgg } })
      });
      if (res.ok) { alert('Change requested'); this.serverEgg = ''; }
      else alert('Request failed');
    },
    editPalette(p) {
      // Clone the palette out so edits are local until saved
      this.editingPalette = JSON.parse(JSON.stringify(p));
    },
    cancelEdit() {
      this.editingPalette = null;
    },
    async saveEdit() {
      if (!this.editingPalette) return;
      const p = this.editingPalette;
      const res = await fetch(`/admin/ofa/themes/${p.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken() },
        body: JSON.stringify({ name: p.name, colors: p.colors, is_default: p.is_default }),
      });
      if (res.ok) {
        this.editingPalette = null;
        await this.fetchPalettes();
      }
    },
    async deletePalette(p) {
      if (!confirm('Delete this palette?')) return;
      const res = await fetch(`/admin/ofa/themes/${p.id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': this.csrfToken() } });
      if (res.ok) await this.fetchPalettes();
    },
    async exportPalette(p) {
      const res = await fetch(`/admin/ofa/themes/${p.id}/export`, { headers: { 'X-CSRF-TOKEN': this.csrfToken() } });
      if (!res.ok) { alert('Export failed'); return; }
      const blob = await res.blob();
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `palette-${p.slug}.json`;
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
    },
    openImport() {
      this.$refs.importFile.click();
    },
    handleFile(e) {
      const file = e.target.files && e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = async () => {
        try {
          const json = JSON.parse(reader.result);
          const res = await fetch('/admin/ofa/themes/import', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken() },
            body: JSON.stringify(json),
          });
          if (res.ok) {
            await this.fetchPalettes();
            alert('Palette imported');
          } else {
            const txt = await res.text();
            alert('Import failed: ' + txt);
          }
        } catch (err) {
          alert('Invalid JSON file');
        }
      };
      reader.readAsText(file);
    },
  },
};
</script>

<style>
.ofa-theme-page { padding: 20px; }
.palettes { display: flex; gap: 12px; flex-wrap: wrap; }
.palette { width: 220px; border: 1px solid #e5e7eb; padding: 8px; border-radius: 6px; }
.preview { height: 80px; border-radius: 4px; }
.meta { padding-top: 8px; display:flex; justify-content:space-between; align-items:center }
.create { margin-top: 24px; }
.color-row { display:flex; gap:8px; align-items:center; margin-top:8px }
.controls { margin-bottom: 12px }

.edit-modal { position: fixed; inset: 0; display:flex; align-items:center; justify-content:center; background: rgba(0,0,0,0.4); }
.edit-modal .modal-content { background: #fff; padding: 18px; border-radius: 8px; min-width: 320px; }
.edit-modal .modal-actions { margin-top: 12px; display:flex; gap:8px; justify-content:flex-end }

</style>
