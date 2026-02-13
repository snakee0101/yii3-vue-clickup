<script setup></script>

<template>
<default-layout>
    <!-- WHITE SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-section">
            <div class="sidebar-title">Home</div>
            <div class="sidebar-item">Inbox</div>
            <div class="sidebar-item">Replies</div>
            <div class="sidebar-item">Assigned Comments</div>
            <div class="sidebar-item">My Tasks</div>
        </div>

        <div class="sidebar-divider"></div>

        <div class="sidebar-section">
            <div class="sidebar-title">Spaces</div>
            <div class="sidebar-item active">Expense tracker</div>
            <div class="sidebar-item">basic features</div>
            <div class="sidebar-item">News</div>
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

            <!-- SECTION 1 -->
            <div class="section">

                <div class="section-header">
                    <div class="section-title">
                        basic features
                    </div>

                    <div class="status-pill gray">
                        TO DO <span>7</span>
                    </div>
                </div>

                <div class="task-table">
                    <div class="task-row header">
                        <div class="col-name">Name</div>
                        <div class="col-due">Due date</div>
                        <div class="col-priority">Priority</div>
                    </div>

                    <div class="task-row">
                        <div class="col-name">expense tracker features для моего проекта</div>
                        <div class="col-due"></div>
                        <div class="col-priority"></div>
                    </div>

                    <div class="task-row">
                        <div class="col-name">примеров проектов expense tracker</div>
                        <div class="col-due"></div>
                        <div class="col-priority"></div>
                    </div>
                </div>

            </div>


            <!-- SECTION 2 -->
            <div class="section">

                <div class="section-header">
                    <div class="section-title">
                        News
                    </div>

                    <div class="status-pill purple">
                        IN PROGRESS <span>1</span>
                    </div>
                </div>

                <div class="task-table">
                    <div class="task-row header">
                        <div class="col-name">Name</div>
                        <div class="col-due">Due date</div>
                        <div class="col-priority">Priority</div>
                    </div>

                    <div class="task-row">
                        <div class="col-name">в базе еще нужно сохранить ссылки на загруженные изображения</div>
                        <div class="col-due"></div>
                        <div class="col-priority"></div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</default-layout>
</template>

<style scoped></style>
