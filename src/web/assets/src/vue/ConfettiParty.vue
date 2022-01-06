<template>
  <div />
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import Confetti from 'vue-confetti/src/confetti.js';

const config: Partial<ConfettiConfig> = {
  defaultType: 'rect',
  defaultSize: 15,
  defaultColors: ['DodgerBlue', 'OliveDrab', 'Gold', 'pink', 'SlateBlue', 'lightblue', 'Violet', 'PaleGreen', 'SteelBlue', 'SandyBrown', 'Chocolate', 'Crimson'],
};

const confetti = ref(new Confetti());

onMounted(() => {
  confetti.value.start(config);
  setTimeout(() => {
    confetti.value.stop();
  }, 5000);
});
</script>
