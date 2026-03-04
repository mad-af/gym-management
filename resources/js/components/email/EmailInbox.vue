<template>
    <div class="sm:h-[calc(100vh-174px)] xl:h-[calc(100vh-186px)]">
        <div class="flex h-full flex-col gap-6 sm:gap-5 xl:grid xl:grid-cols-12">
            <!-- Inbox Sidebar Start -->
            <div class="w-full xl:col-span-3">
                <EmailSidebar />
            </div>

            <!-- Inbox Sidebar End -->

            <!-- Inbox Mailbox Start -->
            <div class="w-full xl:col-span-9">
                <EmailList />
            </div>

            <!-- Inbox Mailbox End -->
        </div>
    </div>
</template>

<script setup lang="ts">
import EmailList from './EmailList.vue';
import EmailSidebar from './EmailSidebar.vue';
</script>
