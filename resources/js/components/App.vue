<template>
    <div class="container">
        <chat></chat>
    </div>
</template>
<script>
import Chat from "./Chat.vue";
</script>
<style>
@import url("https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css");

@font-face {
    font-family: "Golden Plains - Demo";
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}

* {
    font-family: Pretendard, -apple-system, BlinkMacSystemFont, system-ui, Roboto,
    "Helvetica Neue", "Segoe UI", "Apple SD Gothic Neo", "Noto Sans KR",
    "Malgun Gothic", sans-serif;
}

body {
    background-color: #cfcff4;

}

.container {
    height: 812px;
    background-color: #ffffff;
    margin: 5rem auto 0rem;
    border-radius: 1.5rem;
    box-shadow: 0px 1px 20px #9c9cc855;
}
</style>
