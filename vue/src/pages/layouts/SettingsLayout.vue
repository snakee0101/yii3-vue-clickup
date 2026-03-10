<script setup>
import {ref, reactive, watch} from 'vue';
import router from "@/router.js";

let selectedPage = ref('');
let settingsPages = [
  {name: 'General', path: '/settings/general'},
  {name: 'Task Statuses', path: '/settings/task-statuses'}
];

//select initial route in a menu when you open the page
selectedPage.value = settingsPages.find(page => location.href.includes(page.path));

watch(selectedPage, function(newPage) {
  router.push(newPage.path); //when you select a page - dynamically route to it
});

</script>

<template>
<default-layout>
    <!-- WHITE SIDEBAR -->
    <aside class="bg-white">
        <div class="sidebar-section">
          <h2 class="font-bold text-xl p-3!">Settings</h2>
          <Listbox v-model="selectedPage" :options="settingsPages" optionLabel="name" class="w-full md:w-56" />
        </div>
    </aside>


    <!-- MAIN -->
    <div class="main">
        <!-- TOP NAV -->
        <header class="topbar">
            <div class="top-left">
                <div class="workspace-title">
                    Магазин, + EXPENSE TRACKER
                </div>

                <div class="view-tabs">
                    <div class="tab">Overview</div>
                    <div class="tab active">List</div>
                    <div class="tab">Board</div>
                    <div class="tab">Whiteboard</div>
                    <div class="tab">+ View</div>
                </div>
            </div>

            <div class="top-right">
                <input class="search" placeholder="Search Ctrl K" />
                <button class="btn-task">+ Task</button>
            </div>
        </header>

        <!-- CONTENT -->
        <div class="content">
          <slot></slot>
        </div>
    </div>
</default-layout>
</template>

<style scoped></style>
