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
          <a href="#" @click.prevent="logout">Logout</a>
        </div>
    </aside>

    <slot></slot>
</div>
</template>

<style scoped></style>
