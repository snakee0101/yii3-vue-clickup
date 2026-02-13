<script setup></script>

<template>
    <auth-layout>
        <h1>Login</h1>

        <form>
            <input type="email" placeholder="Your email" required />
            <input type="password" placeholder="Your password" required />

            <div class="form-footer">
                <button type="submit">Login</button>
                <router-link to="/register">Click here to register</router-link>
            </div>
        </form>
    </auth-layout>
</template>

<style scoped></style>