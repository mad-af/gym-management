<template>
    <div class="mb-10">
        <div
            class="mb-2 flex flex-wrap justify-center gap-1 text-title-md font-bold text-brand-500 xl:text-title-lg dark:text-brand-400">
            <!-- timer days -->
            <div v-for="(day, index) in daysArray" :key="index">
                <div v-show="day.visible" class="timer-box">
                    <span>{{ day.value }}</span>
                </div>
            </div>

            :

            <!-- timer hours -->
            <div v-for="(hour, index) in hoursArray" :key="index">
                <div v-show="hour.visible" class="timer-box">
                    <span>{{ hour.value }}</span>
                </div>
            </div>

            :

            <!-- timer minutes -->
            <div v-for="(minute, index) in minutesArray" :key="index">
                <div v-show="minute.visible" class="timer-box">
                    <span>{{ minute.value }}</span>
                </div>
            </div>

            :

            <!-- timer seconds -->
            <div v-for="(second, index) in secondsArray" :key="index">
                <div v-show="second.visible" class="timer-box">
                    <span>{{ second.value }}</span>
                </div>
            </div>
        </div>

        <div class="text-center text-base text-gray-500 dark:text-gray-400">
            <div class="flex justify-center gap-0.5">
                <div v-for="(day, index) in daysArray" :key="index">
                    <span v-show="day.visible" class="timer-box inline-block">
                        <span class="inline-block">{{ day.value }}</span>
                    </span>
                </div>
                <div>days left</div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

type Unit = 'days' | 'hours' | 'minutes' | 'seconds';
type TimeDigit = { value: string; visible: boolean; remainingPercentage: number };

const daysArray = ref<TimeDigit[]>([]);
const hoursArray = ref<TimeDigit[]>([]);
const minutesArray = ref<TimeDigit[]>([]);
const secondsArray = ref<TimeDigit[]>([]);
const endTime = new Date('December 20, 2025 23:59:59 GMT+0530').getTime();
const now = ref(new Date().getTime());
const timeLeft = ref(0);

let counter: ReturnType<typeof setInterval> | undefined;

const countdown = () => {
    counter = setInterval(() => {
        now.value = new Date().getTime();
        timeLeft.value = (endTime - now.value) / 1000;

        updateTimeArrays();

        if (timeLeft.value <= 0) {
            clearInterval(counter);
            resetTimeArrays();
        }
    }, 1000);
};

const format = (value: number) => String(Math.floor(value)).padStart(2, '0');

const updateTimeArrays = () => {
    daysArray.value = getTimeArray(timeLeft.value / (60 * 60 * 24), 'days');
    hoursArray.value = getTimeArray((timeLeft.value / (60 * 60)) % 24, 'hours');
    minutesArray.value = getTimeArray((timeLeft.value / 60) % 60, 'minutes');
    secondsArray.value = getTimeArray(timeLeft.value % 60, 'seconds');
};

const getMaxValueForUnit = (unit: Unit) => {
    switch (unit) {
        case 'days':
            return 365;
        case 'hours':
            return 24;
        case 'minutes':
            return 60;
        case 'seconds':
            return 60;
        default:
            return 1;
    }
};

const getTimeArray = (value: number, unit: Unit): TimeDigit[] => {
    const stringValue = format(value);
    const percentage = (value / getMaxValueForUnit(unit)) * 100;
    return stringValue.split('').map((digit) => ({
        value: digit,
        visible: true,
        remainingPercentage: percentage,
    }));
};

const resetTimeArrays = () => {
    daysArray.value = [{ value: '0', visible: true, remainingPercentage: 0 }];
    hoursArray.value = [{ value: '0', visible: true, remainingPercentage: 0 }];
    minutesArray.value = [{ value: '0', visible: true, remainingPercentage: 0 }];
    secondsArray.value = [{ value: '0', visible: true, remainingPercentage: 0 }];
};

onMounted(() => {
    countdown();
});

onUnmounted(() => {
    if (counter) {
        clearInterval(counter);
    }
});
</script>
