<script setup></script>

<template>
<div class="layout">
    <div class="auth-card auth-container">
        <h1>Login</h1>

        <form>
            <input type="email" placeholder="Your email" required />
            <input type="password" placeholder="Your password" required />

            <div class="form-footer">
                <button type="submit">Login</button>
                <router-link to="/register">Click here to register</router-link>
            </div>
        </form>
    </div>
</div>
</template>

<style scoped></style>