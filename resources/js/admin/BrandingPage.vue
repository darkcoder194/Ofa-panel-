<template>
  <div class="ofa-branding-page">
    <h2>Branding</h2>
    <form @submit.prevent="save">
      <div>
        <label>Site Name</label>
        <input v-model="siteName" />
      </div>
      <div>
        <label>Logo</label>
        <input ref="logo" type="file" accept="image/*" />
        <div v-if="logoUrl"><img :src="logoUrl" style="max-height:64px; margin-top:8px" /></div>
      </div>
      <div>
        <label>Wallpaper</label>
        <input ref="wallpaper" type="file" accept="image/*" />
        <div v-if="wallpaperUrl"><img :src="wallpaperUrl" style="max-height:128px; margin-top:8px" /></div>
      </div>
      <div style="margin-top:12px">
        <button type="submit">Save</button>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      siteName: '',
      logoUrl: null,
      wallpaperUrl: null,
    };
  },
  async created() {
    await this.fetch();
  },
  methods: {
    csrfToken() {
      const el = document.querySelector('meta[name="csrf-token"]');
      return el ? el.getAttribute('content') : '';
    },
    async fetch() {
      const res = await fetch('/admin/ofa/branding');
      if (res.ok) {
        const json = await res.json();
        this.siteName = json.site_name || '';
        if (json.logo) this.logoUrl = `/storage/${json.logo}`;
        if (json.wallpaper) this.wallpaperUrl = `/storage/${json.wallpaper}`;
      }
    },
    async save() {
      const form = new FormData();
      form.append('site_name', this.siteName);
      if (this.$refs.logo.files && this.$refs.logo.files[0]) form.append('logo', this.$refs.logo.files[0]);
      if (this.$refs.wallpaper.files && this.$refs.wallpaper.files[0]) form.append('wallpaper', this.$refs.wallpaper.files[0]);

      const res = await fetch('/admin/ofa/branding', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': this.csrfToken() },
        body: form,
      });

      if (res.ok) {
        alert('Saved');
        await this.fetch();
      } else {
        alert('Save failed');
      }
    }
  }
};
</script>

<style>
.ofa-branding-page { padding: 20px; }
</style>
