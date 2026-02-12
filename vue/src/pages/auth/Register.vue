<script setup></script>

<template>
<div class="auth-container">
    <div class="auth-card">
        <h1>Register</h1>

        <form>
            <input type="text" placeholder="Your name" required />
            <input type="email" placeholder="Your email" required />
            <input type="password" placeholder="Your password" required />

            <div class="form-footer">
                <button type="submit">Register</button>
                <router-link to="/login">Click here to login</router-link>
            </div>
        </form>
    </div>
</div>
</template>

<style scoped></style>