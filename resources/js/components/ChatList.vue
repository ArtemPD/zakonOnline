<template>
  <div class="chat__body" id="chat__body">
    <chat-message
      v-for="(msg, index) in msgs"
      :key="index"
      :msg="msg"
      :prev="[index == 0 ? null : msgs[index - 1]]"
    >
    </chat-message>
  </div>
</template>

<script>
import ChatMessage from "./ChatMessage.vue";
export default {
  components: {
    ChatMessage,
  },
  props: ["msgs"],
};
</script>

<style>
.chat__body {
  padding: 0 3rem;
  overflow: scroll;
  scroll-behavior: smooth;
}

.chat__body::-webkit-scrollbar {
  display: none;
}
</style>
