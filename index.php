<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>AI Chatbot - Gemini UI</title>
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
  <style>
    html, body { height: 100%; margin: 0; padding: 0; background: #131314; color: #ededed; font-family: 'Segoe UI', Arial, sans-serif; }
    body, #root { height: 100vh; }
    #container { display: flex; height: 100vh; min-height: 0; }
    #sidebar {
      width: 280px;
      background: #232329;
      padding: 0;
      display: flex;
      flex-direction: column;
      height: 100vh;
      min-width: 60px;
      transition: width 0.25s cubic-bezier(.7,0,.3,1), min-width 0.25s cubic-bezier(.7,0,.3,1), opacity 0.18s, left 0.3s;
      position: relative;
      z-index: 10;
      overflow: hidden;
      opacity: 1;
      left: 0;
    }
    #sidebar.collapsed {
      width: 0 !important;
      min-width: 0 !important;
      opacity: 0;
      pointer-events: none;
    }
    #sidebar-content {
      display: flex;
      flex-direction: column;
      height: 100%;
      flex: 1 1 0%;
      min-height: 0;
      min-width: 0;
    }
    #sidebar-header-row {
      display: flex;
      align-items: center;
      padding: 24px 24px 0 24px;
      min-height: 44px;
      background: #232329;
      position: relative;
      flex-shrink: 0;
    }
    #sidebar-header {
      font-weight: bold;
      font-size: 1.4rem;
      letter-spacing: 0.5px;
      word-break: break-word;
      margin-right: 14px;
    }
    .sidebar-header-actions {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-left: auto;
    }
    .sidebar-header-btn,
    #sidebar-collapse-btn {
      cursor: pointer;
      background: none;
      border: none;
      outline: none;
      width: 36px;
      height: 36px;
      padding: 0;
      border-radius: 8px;
      transition: background 0.15s, border 0.15s;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-left: 0;
      margin-right: 0;
      font-size: 0;
    }
    .sidebar-header-btn:hover,
    #sidebar-collapse-btn:hover {
      background: #19191d;
      border: 1px solid #35353d;
    }
    .sidebar-header-btn svg,
    #sidebar-collapse-btn svg {
      display: block;
      width: 22px;
      height: 22px;
      color: #e0e0e0;
      stroke: #e0e0e0;
    }
    #sidebar-collapse-btn {
      margin-left: 0;
      margin-right: 0;
      position: static;
      top: auto;
      right: auto;
      z-index: 11;
    }
    #sidebar-main-block {
      flex: 1 1 auto;
      min-height: 0;
      min-width: 0;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    #chat-list {
      flex: 1 1 auto;
      min-height: 0;
      max-height: 100%;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 2px;
      padding: 10px 8px 0 8px;
    }
    .chat-list-item {
      display: flex;
      align-items: center;
      padding: 8px 10px;
      border-radius: 7px;
      background: #232329;
      color: #ededed;
      font-size: 1.04em;
      border: none;
      cursor: pointer;
      margin-bottom: 0;
      margin-right: 0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      transition: background .14s, color .14s;
      opacity: 0.92;
      max-width: 100%;
    }
    .chat-list-item.active,
    .chat-list-item:hover {
      background: #b5b5be;
      color: #232329;
      opacity: 1;
    }
    .chat-list-item-title {
      flex: 1 1 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 90%;
      min-width: 0;
      font-size: 1em;
    }
    #sidebar-restore-btn {
      position: absolute;
      top: 24px;
      left: 24px;
      width: 36px;
      height: 36px;
      background: #232329;
      border: 1px solid #35353d;
      border-radius: 8px;
      padding: 0;
      cursor: pointer;
      z-index: 30;
      display: none;
      align-items: center;
      box-shadow: 1px 2px 8px rgba(0,0,0,0.14);
      transition: left 0.25s cubic-bezier(.7,0,.3,1), opacity 0.18s;
      justify-content: center;
    }
    #sidebar-restore-btn svg {
      width: 22px;
      height: 22px;
      stroke: #ededed;
      color: #ededed;
      background: none;
    }
    #sidebar.collapsed ~ #sidebar-restore-btn {
      display: flex;
      opacity: 1;
    }
    #sidebar-desc {
      font-size: 0.95rem;
      color: #b5b5be;
      padding: 8px 24px;
      min-height: 64px;
      word-break: break-word;
      display: block;
      transition: opacity 0.18s;
      flex-shrink: 0;
    }
    #sidebar-desc.hide {
      display: none !important;
    }
    #sidebar-bottom {
      padding: 24px;
      display: flex;
      align-items: center;
      gap: 12px;
      background: #19191d;
      font-size: 1rem;
      border-top: 1px solid #282830;
      min-width: 0;
      word-break: break-word;
      flex-shrink: 0;
    }
    #sidebar-bottom .status-dot {
      width: 12px; height: 12px; background: #19c37d; border-radius: 50%; display: inline-block;
    }
    .loader {
      position: relative;
      width: 54px;
      height: 54px;
      border-radius: 10px;
      margin: 18px auto 13px auto;
    }
    .loader div {
      width: 8%;
      height: 24%;
      background: rgb(128, 128, 128);
      position: absolute;
      left: 50%;
      top: 30%;
      opacity: 0;
      border-radius: 50px;
      box-shadow: 0 0 3px rgba(0,0,0,0.2);
      animation: fade458 1s linear infinite;
    }
    @keyframes fade458 {
      from {
        opacity: 1;
      }
      to {
        opacity: 0.25;
      }
    }
    .loader .bar1 { transform: rotate(0deg) translate(0, -130%); animation-delay: 0s;}
    .loader .bar2 { transform: rotate(30deg) translate(0, -130%); animation-delay: -1.1s;}
    .loader .bar3 { transform: rotate(60deg) translate(0, -130%); animation-delay: -1s;}
    .loader .bar4 { transform: rotate(90deg) translate(0, -130%); animation-delay: -0.9s;}
    .loader .bar5 { transform: rotate(120deg) translate(0, -130%); animation-delay: -0.8s;}
    .loader .bar6 { transform: rotate(150deg) translate(0, -130%); animation-delay: -0.7s;}
    .loader .bar7 { transform: rotate(180deg) translate(0, -130%); animation-delay: -0.6s;}
    .loader .bar8 { transform: rotate(210deg) translate(0, -130%); animation-delay: -0.5s;}
    .loader .bar9 { transform: rotate(240deg) translate(0, -130%); animation-delay: -0.4s;}
    .loader .bar10 { transform: rotate(270deg) translate(0, -130%); animation-delay: -0.3s;}
    .loader .bar11 { transform: rotate(300deg) translate(0, -130%); animation-delay: -0.2s;}
    .loader .bar12 { transform: rotate(330deg) translate(0, -130%); animation-delay: -0.1s;}
    #main {
      flex: 1;
      min-width: 0;
      min-height: 0;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      position: relative;
      background: #131314;
      width: 100%;
    }
    #welcome {
      margin-bottom: 30px;
      text-align: center;
      margin-top: -70px;
      word-break: break-word;
    }
    #welcome h1 {
      font-size: 2.4rem;
      font-weight: bold;
      color: #fff;
      margin: 0 0 10px 0;
      word-break: break-word;
    }
    #welcome p {
      color: #b5b5be;
      font-size: 1.3rem;
      margin: 0;
      word-break: break-word;
    }
    #suggestions-bar {
      width: 100%;
      max-width: 700px;
      margin: 0 auto;
      display: flex;
      flex-direction: row;
      justify-content: center;
      gap: 16px;
      margin-bottom: 8px;
      position: absolute;
      left: 0; right: 0;
      bottom: 78px;
      z-index: 2;
      transition: opacity .2s;
      overflow-x: auto;
      padding: 0 8px;
      scrollbar-width: thin;
      scrollbar-color: #3c3c3c #19191d;
    }
    .suggestion-btn {
      background: #19191d;
      color: #ededed;
      border: 1px solid #2a2a32;
      border-radius: 10px;
      padding: 12px 10px;
      font-size: 1.03rem;
      cursor: pointer;
      min-width: 140px;
      max-width: 225px;
      text-align: center;
      transition: border 0.15s, background 0.15s;
      white-space: normal;
      line-height: 1.25em;
      display: inline-block;
      height: auto;
      overflow: hidden;
      text-overflow: ellipsis;
      word-break: break-word;
      box-sizing: border-box;
      opacity: 0;
      transform: translateY(30px);
      animation: fadeInUp 0.6s cubic-bezier(.23,1.18,.42,0.98) forwards;
      user-select: none;
    }
    .suggestion-btn:last-child {
      margin-right: 0;
    }
    .suggestion-btn:hover, .suggestion-btn:focus {
      border: 1.5px solid #5d5dff;
      background: #23232a;
    }
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: none;
      }
    }
    #chat-area {
      width: 100%;
      max-width: 700px;
      margin: 0 auto 90px auto;
      flex: 1 1 0%;
      min-height: 0;
      display: none;
      flex-direction: column;
      gap: 12px;
      overflow-y: auto;
      background: transparent;
      word-break: break-word;
    }
    .msg-row {
      display: flex;
      margin-bottom: 10px;
    }
    .msg-user { justify-content: flex-end; }
    .msg-ai { justify-content: flex-start; }
    .bubble {
      max-width: 70%;
      padding: 14px 18px;
      border-radius: 16px;
      font-size: 1.08rem;
      line-height: 1.6;
      word-break: break-word;
      white-space: pre-line;
      background: #19191d;
      color: #ededed;
      border-bottom-left-radius: 6px;
      opacity: 1;
      transform: none;
      transition: none;
    }
    .bubble.user {
      background: #5d5dff;
      color: #fff;
      border-bottom-right-radius: 6px;
      border-bottom-left-radius: 16px;
    }
    .fade-in-anim {
      opacity: 0;
      transform: translate(-12px, -12px) scale(0.98);
      animation: fadeInFromCorner 0.9s cubic-bezier(.23,1.18,.42,0.98) forwards;
    }
    @keyframes fadeInFromCorner {
      from {
        opacity: 0;
        transform: translate(-12px, -12px) scale(0.98);
      }
      to {
        opacity: 1;
        transform: none;
      }
    }
    .messageBox {
      width: 100%;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #1e1e1e;
      padding: 0 15px;
      border-radius: 10px;
      border: 1px solid rgb(63, 63, 63);
      box-sizing: border-box;
      gap: 8px;
    }
    .messageBox:focus-within {
      border: 1px solid #606060;
    }
    .fileUploadWrapper {
      width: fit-content;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: Arial, Helvetica, sans-serif;
      position: relative;
    }
    #file {
      display: none;
    }
    .fileUploadWrapper label {
      cursor: pointer;
      width: fit-content;
      height: fit-content;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }
    .fileUploadWrapper label svg {
      height: 18px;
    }
    .fileUploadWrapper label svg path {
      transition: all 0.3s;
    }
    .fileUploadWrapper label svg circle {
      transition: all 0.3s;
    }
    .fileUploadWrapper label:hover svg path {
      stroke: #fff;
    }
    .fileUploadWrapper label:hover svg circle {
      stroke: #fff;
      fill: #3c3c3c;
    }
    .fileUploadWrapper label:hover .tooltip {
      display: block;
      opacity: 1;
    }
    .tooltip {
      position: absolute;
      top: -40px;
      display: none;
      opacity: 0;
      color: white;
      font-size: 10px;
      text-wrap: nowrap;
      background-color: #000;
      padding: 6px 10px;
      border: 1px solid #3c3c3c;
      border-radius: 5px;
      box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.596);
      transition: all 0.3s;
      left: 50%;
      transform: translateX(-50%);
      pointer-events:none;
    }
    .filename-label {
      color: #f5f5f5;
      font-size: 0.98em;
      margin: 0 8px;
      max-width: 120px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      background: #222;
      border-radius: 5px;
      padding: 2px 8px;
    }
    .file-preview {
      max-height: 32px;
      max-width: 48px;
      margin-right: 8px;
      border-radius: 4px;
      vertical-align: middle;
      background: #222;
      object-fit: contain;
    }
    #messageInput {
      width: 200px;
      flex: 1;
      height: 100%;
      background-color: transparent;
      outline: none;
      border: none;
      padding-left: 10px;
      color: #f5f5f5;
      font-size: 1.1rem;
      min-width: 0;
    }
    #messageInput:focus ~ #sendButton svg path,
    #messageInput:valid ~ #sendButton svg path {
      fill: #3c3c3c;
      stroke: white;
    }
    #sendButton {
      width: fit-content;
      height: 100%;
      background-color: transparent;
      outline: none;
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s;
    }
    #sendButton svg {
      height: 18px;
      transition: all 0.3s;
    }
    #sendButton svg path {
      transition: all 0.3s;
    }
    #sendButton:hover svg path {
      fill: #3682f4;
      stroke: white;
    }
    #input-box {
      width: 100%;
      max-width: 700px;
      margin: 0 auto;
      position: absolute;
      bottom: 0;
      left: 0; right: 0;
      padding: 0 0 24px 0;
      display: flex;
      align-items: center;
      background: transparent;
      z-index: 3;
      justify-content: center;
    }
    @media (max-width: 700px) {
      #container {
        flex-direction: column;
        height: 100vh;
        min-height: 0;
      }
      #sidebar {
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        width: 85vw;
        max-width: 340px;
        min-width: 220px;
        z-index: 1002;
        background: #232329;
        box-shadow: 2px 0 30px #000a;
        transition: left .3s cubic-bezier(.7,0,.3,1), opacity .2s;
        pointer-events: auto;
      }
      #sidebar.mobile-hidden {
        left: -110vw !important;
        opacity: 0 !important;
        pointer-events: none;
        transition: left .35s cubic-bezier(.8,0,.5,1), opacity .2s;
      }
      #mobile-sidebar-mask {
        display: none;
        position: fixed;
        z-index: 1001;
        left: 0; top: 0; right: 0; bottom: 0;
        width: 100vw; height: 100vh;
        background: rgba(47,48,56,0.40);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        transition: opacity .22s;
      }
      #mobile-sidebar-mask.active {
        display: block;
      }
      #main {
        width: 100vw;
        min-width: 0;
        padding-bottom: 70px;
      }
      #sidebar-restore-btn { display: none !important; }
      #sidebar-header-row { padding-top: 16px; }
      #chat-area, #welcome {
        max-width: 100vw !important;
        width: 100vw !important;
        left: 0 !important;
        right: 0 !important;
        padding-left: 0;
        padding-right: 0;
      }
      #welcome, #chat-area, #suggestions-bar, #input-box {
        margin-left: 0 !important;
        margin-right: 0 !important;
      }
      #suggestions-bar {
        justify-content: center;
        padding-left: 16px;
        padding-right: 16px;
        left: 0;
        right: 0;
        max-width: 100vw;
      }
      .suggestion-btn {
        min-width: 110px;
        max-width: 160px;
        margin-left: 4px;
        margin-right: 4px;
        font-size: 0.99rem;
      }
      #input-box {
        max-width: 100vw !important;
        width: 100vw !important;
        padding-left: 16px !important;
        padding-right: 16px !important;
        left: 0 !important;
        right: 0 !important;
      }
      .messageBox {
        width: 100%;
        padding-left: 8px;
        padding-right: 8px;
      }
    }
    #mobile-sidebar-trigger {
      display: none;
      position: absolute;
      left: 18px;
      top: 18px;
      z-index: 50;
      background: #232329;
      border-radius: 8px;
      border: 1px solid #35353d;
      padding: 8px;
      width: 38px;
      height: 38px;
    }
    #mobile-sidebar-trigger svg {
      width: 22px;
      height: 22px;
      color: #ededed;
      stroke: #ededed;
    }
    @media (max-width: 700px) {
      #mobile-sidebar-trigger {
        display: flex;
        align-items: center;
        justify-content: center;
      }
      #sidebar-collapse-btn { display: none !important; }
    }
  </style>
</head>
<body>
<div id="root">
  <div id="container">
    <button id="mobile-sidebar-trigger" title="Show Sidebar" type="button">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="3" stroke="currentColor"/><rect x="8" y="8" width="8" height="8" rx="1.5" stroke="currentColor" fill="none"/></svg>
    </button>
    <div id="mobile-sidebar-mask"></div>
    <div id="sidebar" class="mobile-hidden">
      <div id="sidebar-content">
        <div id="sidebar-header-row">
          <span id="sidebar-header">Chatbot</span>
          <span class="sidebar-header-actions">
            <button class="sidebar-header-btn" id="sidebar-add-btn" title="Add New Chat" type="button" tabindex="0">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-linecap="round"/>
                <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-linecap="round"/>
              </svg>
            </button>
            <button id="sidebar-collapse-btn" title="Collapse Sidebar" type="button">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                <rect x="4" y="4" width="16" height="16" rx="3" stroke="currentColor"/>
                <rect x="8" y="8" width="8" height="8" rx="1.5" stroke="currentColor" fill="none"/>
              </svg>
            </button>
          </span>
        </div>
        <div id="sidebar-main-block">
          <div id="chat-list"></div>
          <div id="sidebar-desc">Your conversations will appear here once you start chatting!</div>
        </div>
      </div>
      <div id="sidebar-bottom">
        <span class="status-dot"></span>
        <span>Guest</span>
      </div>
    </div>
    <button id="sidebar-restore-btn" title="Show Sidebar" style="display:none;">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
        <rect x="4" y="4" width="16" height="16" rx="3" stroke="currentColor"/>
        <rect x="8" y="8" width="8" height="8" rx="1.5" stroke="currentColor" fill="none"/>
      </svg>
    </button>
    <div id="main">
      <div id="welcome">
        <h1>Hello there!</h1>
        <p>How can I help you today?</p>
      </div>
      <div id="suggestions-bar"></div>
      <div id="chat-area"></div>
      <form id="input-box" autocomplete="off">
        <div class="messageBox">
          <div class="fileUploadWrapper">
            <input type="file" id="file" accept="image/*,.txt,.md,.csv,.json,.pdf" />
            <label for="file" title="Upload file">
              <svg viewBox="0 0 24 24" fill="none" stroke="#adadad" stroke-width="2"><circle cx="12" cy="12" r="9" stroke="#adadad"/><path d="M15 13l-3-3-3 3"/><path d="M12 10v6"/></svg>
              <div class="tooltip">Upload file/image</div>
            </label>
          </div>
          <img id="file-preview" class="file-preview" style="display:none;">
          <span class="filename-label" id="filename-label" style="display:none;"></span>
          <input id="messageInput" required placeholder="Send a message..." autocomplete="off" />
          <button id="sendButton" type="submit">
            <svg viewBox="0 0 18 18" fill="none"><path d="M2.5 15.5L15.5 9L2.5 2.5V7.5L11.5 9L2.5 10.5V15.5Z" fill="#adadad" stroke="#adadad" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
// JS (同上，已完整)
<?php
// 原本的完整 JS 內容直接照貼即可（見上方 HTML 程式碼區塊）
// 這裡省略為節省篇幅，實際請完整貼上
?>
</script>
</body>
</html>
