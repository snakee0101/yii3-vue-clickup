<script setup>

function logout() {
  localStorage.removeItem('access_token');
  window.location = '/login';
}

</script>

<template>
<div class="layout">
    <!-- SIDEBAR -->
    <aside class="rail">
        <div class="rail-logo">W</div>

        <div class="rail-icons">
            <router-link to="/">Spaces</router-link>
            <router-link to="/goals">Goals</router-link>
            <div class="rail-icon"></div>
            <div class="rail-icon"></div>
            <div class="rail-icon"></div>
        </div>

        <div class="logout">
          <div>
            <router-link to="/settings/general">Settings</router-link>
          </div>
          <div>
            <a href="#" @click.prevent="logout">Logout</a>
          </div>
        </div>
    </aside>

    <slot></slot>
</div>
</template>

<style scoped></style>
