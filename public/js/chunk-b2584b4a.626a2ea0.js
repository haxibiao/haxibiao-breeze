(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-b2584b4a"],{"035c":function(e,t,a){"use strict";a.r(t);var s=a("7a23");Object(s["Q"])("data-v-4b7e30d4");var c={class:"chat-page"},n={class:"fluid-content"},l={class:"container"},i={class:"left"},o={class:"list-header text-ellipsis"},d=Object(s["n"])("span",null,"聊天列表",-1),u={class:"right"},r={class:"message-header"},b={class:"send-box"},v=Object(s["n"])("div",{class:"operation-row"},[Object(s["n"])("label",{class:"image-upload-btn"}),Object(s["n"])("label",{class:"emotion-btn-box"})],-1),j={class:"send-input"},O={class:"flex-end-send-btn"},g={class:"indicator"},m=["disabled"],f=Object(s["n"])("span",{class:"btn-title"},"发送",-1),h=[f];function p(e,t,a,f,p,y){var w,M,S,k=Object(s["W"])("Masthead"),x=Object(s["W"])("ChatsList"),N=Object(s["W"])("MessagesView"),_=Object(s["W"])("el-input");return Object(s["N"])(),Object(s["m"])(s["b"],null,[Object(s["r"])(k),Object(s["n"])("div",c,[Object(s["n"])("div",n,[Object(s["n"])("div",l,[Object(s["n"])("div",i,[Object(s["n"])("div",o,[Object(s["n"])("i",{class:"el-icon-arrow-left back-icon",onClick:t[0]||(t[0]=function(t){return e.$router.go(-1)})}),d]),Object(s["r"])(x,{"show-message-view":f.showMessageView,"with-chat-id":null===(w=f.chat)||void 0===w?void 0:w.id},null,8,["show-message-view","with-chat-id"])]),Object(s["n"])("div",u,[Object(s["n"])("div",r,Object(s["ab"])(y.chatName),1),Object(s["r"])(N,{ref:"messagesViewRef","chat-id":null===(M=f.chat)||void 0===M?void 0:M.id,"message-status":f.messageStatus},null,8,["chat-id","message-status"]),Object(s["n"])("div",b,[v,Object(s["n"])("div",j,[Object(s["r"])(_,{modelValue:f.content,"onUpdate:modelValue":t[1]||(t[1]=function(e){return f.content=e}),clearable:"",maxlength:"300",type:"textarea"},null,8,["modelValue"])]),Object(s["n"])("div",O,[Object(s["n"])("div",g,Object(s["ab"])((null===(S=f.content)||void 0===S?void 0:S.length)||0)+"/300",1),Object(s["n"])("button",{class:Object(s["B"])(["send-button",f.content&&"active"]),disabled:!f.content,onClick:t[2]||(t[2]=function(){return f.sendMessage&&f.sendMessage.apply(f,arguments)})},h,10,m)])])])])])])],64)}Object(s["O"])();var y=a("3835"),w=a("1da1"),M=a("5530"),S=(a("96cf"),a("d3b7"),a("b0c0"),a("5502")),k=a("4995"),x=a("f685"),N=a("f3e7"),_=a("a684");Object(s["Q"])("data-v-25d7c6f8");var I={key:0,class:"msg-center-tips msg-date"},C=["src"],D={class:"flex-row-align-center"},V={key:0,class:Object(s["B"])(["send-status","right"])},P={key:0,class:"el-icon-loading"},B={key:1,class:"el-icon-warning send-error-icon"},H={key:1,class:"message-text"},L={key:2,class:"message-image"},U=["src"],W={key:3,class:Object(s["B"])(["send-status","left"])},Q={key:0,class:"el-icon-loading"},R={key:1,class:"el-icon-warning send-error-icon"},A=["src"];function T(e,t,a,c,n,l){var i,o,d,u,r,b,v,j,O,g,m,f,h,p,y,w,M,S,k;return Object(s["N"])(),Object(s["m"])("div",null,[c.date?(Object(s["N"])(),Object(s["m"])("div",I,Object(s["ab"])(c.date),1)):Object(s["l"])("",!0),Object(s["n"])("div",{class:Object(s["B"])(["message-item",c.isSelf(null===(i=c.message)||void 0===i||null===(o=i.user)||void 0===o?void 0:o.id)&&"message-item-right"])},[c.isSelf(null===(d=c.message)||void 0===d||null===(u=d.user)||void 0===u?void 0:u.id)?Object(s["l"])("",!0):(Object(s["N"])(),Object(s["m"])("img",{key:0,src:null===(r=c.message)||void 0===r||null===(b=r.user)||void 0===b?void 0:b.avatar,alt:"avatar",class:"message-avatar"},null,8,C)),Object(s["n"])("div",D,[c.isSelf(null===(v=c.message)||void 0===v||null===(j=v.user)||void 0===j?void 0:j.id)?(Object(s["N"])(),Object(s["m"])("div",V,[c.messageStatus.loading?(Object(s["N"])(),Object(s["m"])("i",P)):c.messageStatus.error?(Object(s["N"])(),Object(s["m"])("i",B)):Object(s["l"])("",!0)])):Object(s["l"])("",!0),"text"===(null===(O=c.message)||void 0===O?void 0:O.type)?(Object(s["N"])(),Object(s["m"])("div",H,Object(s["ab"])(null===(g=c.message)||void 0===g?void 0:g.message),1)):"image"===(null===(m=c.message)||void 0===m?void 0:m.type)?(Object(s["N"])(),Object(s["m"])("div",L,[Object(s["n"])("img",{src:null===(f=c.message)||void 0===f||null===(h=f.body)||void 0===h?void 0:h.url,alt:"图片",class:"message-img"},null,8,U)])):Object(s["l"])("",!0),c.isSelf(null===(p=c.message)||void 0===p||null===(y=p.user)||void 0===y?void 0:y.id)?Object(s["l"])("",!0):(Object(s["N"])(),Object(s["m"])("div",W,[c.messageStatus.loading?(Object(s["N"])(),Object(s["m"])("i",Q)):c.messageStatus.error?(Object(s["N"])(),Object(s["m"])("i",R)):Object(s["l"])("",!0)]))]),c.isSelf(null===(w=c.message)||void 0===w||null===(M=w.user)||void 0===M?void 0:M.id)?(Object(s["N"])(),Object(s["m"])("img",{key:1,src:null===(S=c.message)||void 0===S||null===(k=S.user)||void 0===k?void 0:k.avatar,alt:"avatar",class:"message-avatar"},null,8,A)):Object(s["l"])("",!0)],2)])}Object(s["O"])();a("a9e3");var E=a("3bd2"),F={props:{message:Object,index:Number,messageStatus:Object,messagesData:{type:Array,default:[]}},setup:function(e){var t,a,c=Object(s["db"])(e),n=c.message,l=c.index,i=c.messageStatus,o=c.messagesData,d=Object(S["b"])(),u=Object(s["S"])(null===(t=d.state)||void 0===t||null===(a=t.user)||void 0===a?void 0:a.user),r=function(e){var t;return!!e&&e===(null===(t=u.value)||void 0===t?void 0:t.id)},b=Object(s["i"])((function(){var e,t,a;return E["a"].messageDateStr(null===(e=n.value)||void 0===e?void 0:e.created_at,(null===(t=o.value)||void 0===t||null===(a=t[l.value-1])||void 0===a?void 0:a.created_at)||"")}));return{message:n,index:l,messageStatus:i,messagesData:o,date:b,isSelf:r}}};a("74d6");F.render=T,F.__scopeId="data-v-25d7c6f8";var J=F;Object(s["Q"])("data-v-03d3dd01");var z={class:"chats-wrap"},X={key:0,class:"loading-box"},$={key:1,class:"chats-list","infinite-scroll-disabled":"!hasMorePages"},q=["onClick"],G=["onClick"],K=Object(s["n"])("i",{class:"el-icon-close"},null,-1),Y=[K],Z={class:"avatar-box"},ee=["src"],te={class:"chat-info"},ae={class:"user-name text-ellipsis"},se={class:"last-message text-ellipsis"};function ce(e,t,a,c,n,l){var i,o=Object(s["W"])("Skeleton"),d=Object(s["W"])("LoadingSpinner"),u=Object(s["X"])("infinite-scroll");return Object(s["N"])(),Object(s["m"])("div",z,[c.loading?(Object(s["N"])(),Object(s["m"])("div",X,[Object(s["r"])(o,{type:"comments"})])):null!==(i=c.chatsData)&&void 0!==i&&i.length?Object(s["mb"])((Object(s["N"])(),Object(s["m"])("ul",$,[(Object(s["N"])(!0),Object(s["m"])(s["b"],null,Object(s["U"])(c.chatsData,(function(e){var t,a,n;return Object(s["N"])(),Object(s["m"])("li",{key:null===e||void 0===e?void 0:e.id,class:Object(s["B"])(["chat-item",c.withChatId===(null===e||void 0===e?void 0:e.id)&&"active"]),onClick:function(t){return c.showMessageView(e)}},[Object(s["n"])("div",{class:"close-icon",onClick:function(t){return l.removeChat(e)}},Y,8,G),Object(s["n"])("div",Z,[Object(s["n"])("img",{src:null===e||void 0===e||null===(t=e.withUser)||void 0===t?void 0:t.avatar,alt:"avatar",class:"user-avatar"},null,8,ee)]),Object(s["n"])("div",te,[Object(s["n"])("div",ae,Object(s["ab"])((null===e||void 0===e?void 0:e.subject)||(null===e||void 0===e||null===(a=e.withUser)||void 0===a?void 0:a.name)),1),Object(s["n"])("p",se,Object(s["ab"])(null===e||void 0===e||null===(n=e.lastMessage)||void 0===n?void 0:n.message),1)])],10,q)})),128))],512)),[[u,c.loadMore]]):Object(s["l"])("",!0),c.loadingMore?(Object(s["N"])(),Object(s["k"])(d,{key:2})):Object(s["l"])("",!0)])}Object(s["O"])();var ne=a("2909"),le=(a("c740"),a("a434"),a("2d5b")),ie=a("d76c"),oe={props:{showMessageView:Function,withChatId:Number},components:{Skeleton:le["a"],LoadingSpinner:ie["a"]},setup:function(e){var t,a,c,n,l=Object(s["db"])(e),i=l.showMessageView,o=l.withChatId,d=Object(S["b"])(),u=Object(s["S"])(null===(t=d.state)||void 0===t||null===(a=t.user)||void 0===a?void 0:a.user),r=Object(s["S"])(),b=Object(_["P"])({user_id:null===(c=u.value)||void 0===c?void 0:c.id},{enabled:!(null===(n=u.value)||void 0===n||!n.id)}),v=b.chats,j=b.loading,O=b.hasMorePages,g=b.loadMore,m=b.loadingMore;return Object(s["jb"])(v,(function(e){r.value=Object(ne["a"])(e),i.value((null===e||void 0===e?void 0:e[0])||[])})),{withChatId:o,chatsData:r,loading:j,hasMorePages:O,loadMore:g,loadingMore:m,showMessageView:i}},methods:{removeChat:function(e){var t,a=this.chatsData.findIndex((function(t){return t.id===e.id}));null===(t=this.chatsData)||void 0===t||t.splice(a,1)}}};a("c410");oe.render=ce,oe.__scopeId="data-v-03d3dd01";var de=oe;Object(s["Q"])("data-v-67536c1b");var ue={id:"message-list",class:"message-list"},re={key:0,class:"el-icon-loading"},be={key:1},ve={key:0};function je(e,t,a,c,n,l){var i,o=Object(s["W"])("MessageItem");return Object(s["N"])(),Object(s["m"])("div",ue,[c.chatId?(Object(s["N"])(),Object(s["m"])(s["b"],{key:0},[Object(s["n"])("div",{class:"msg-center-tips",onClick:t[0]||(t[0]=function(){return c.loadLastPageMessages&&c.loadLastPageMessages.apply(c,arguments)})},[c.loading||c.loadingMore?(Object(s["N"])(),Object(s["m"])("i",re)):(Object(s["N"])(),Object(s["m"])("div",be,Object(s["ab"])(c.hasMorePages?"加载更多消息":"没有更多消息了~"),1))]),null!==(i=c.messagesData)&&void 0!==i&&i.length?(Object(s["N"])(),Object(s["m"])("div",ve,[(Object(s["N"])(!0),Object(s["m"])(s["b"],null,Object(s["U"])(c.messagesData,(function(e,t){return Object(s["N"])(),Object(s["m"])("div",{key:null===e||void 0===e?void 0:e.id},[Object(s["r"])(o,{message:e,index:t,"message-status":c.messageStatus,"messages-data":c.messagesData},null,8,["message","index","message-status","messages-data"])])})),128))])):Object(s["l"])("",!0)],64)):Object(s["l"])("",!0)])}Object(s["O"])();a("99af");var Oe={props:{chatId:Number,messageStatus:Object},components:{MessageItem:J},setup:function(e){var t=Object(s["db"])(e),a=t.chatId,c=t.messageStatus,n=Object(_["O"])({chat_id:a},{enabled:!!a.value}),l=n.messages,i=n.refetch,o=n.loading,d=n.currentPage,u=n.hasMorePages,r=n.disabled,b=n.loadMore,v=n.loadingMore,j=Object(s["S"])(l.value);function O(){r.value||b()}var g=Object(s["S"])(0);function m(e){var t=document.getElementById("message-list");setTimeout((function(){"bottom"===e?t.scrollHeight>t.clientHeight&&(t.scrollTop=t.scrollHeight):t.scrollHeight>t.clientHeight&&g.value>0&&(t.scrollTop=Number(t.scrollHeight-g.value)),g.value=t.scrollHeight}),0)}function f(e){"[object Object]"===Object.prototype.toString.call(e)&&(j.value=j.value.concat(e)),m("bottom")}return Object(s["jb"])(a,(function(e){i()})),Object(s["jb"])(l,(function(){j.value=l.value,1==d.value?m("bottom"):m(),console.log("messagesData",j.value)})),{chatId:a,messagesData:j,loading:o,loadingMore:v,hasMorePages:u,loadLastPageMessages:O,messageStatus:c,sendMessageSuccess:f}}};a("cedf");Oe.render=je,Oe.__scopeId="data-v-67536c1b";var ge=Oe,me={__typename:"Message",body:{__typename:"MessageBody",text:"",url:null},created_at:"",id:1,message:"",messageable:null,messageable_id:null,messageable_type:null,time_ago:"1秒前",type:"text",user:{}},fe={components:{MessageItem:J,ChatsList:de,MessagesView:ge},setup:function(){var e,t,a=Object(S["b"])(),c=Object(s["S"])(null===(e=a.state)||void 0===e||null===(t=e.user)||void 0===t?void 0:t.user),n=Object(s["S"])({id:46,user_id:null,subject:"",withUser:{name:""}});function l(e){var t;null!==e&&void 0!==e&&e.id&&(n.value=e,console.log("chat.value.id",null===(t=n.value)||void 0===t?void 0:t.id))}var i=Object(s["S"])(""),o=Object(s["S"])(""),d=Object(s["S"])("text"),u=Object(s["S"])({loading:!1,error:""}),r=Object(x["c"])(_["z"],(function(){var e,t;return{variables:{user_id:null===(e=c.value)||void 0===e?void 0:e.id,chat_id:null===(t=n.value)||void 0===t?void 0:t.id,message:"text"===d.value?i.value:"",url:"text"===d.value?"":o.value}}})),b=r.mutate,v=Object(s["S"])(null);function j(e){var t,a=e?{type:"text",message:i.value,body:Object(M["a"])(Object(M["a"])({},null===me||void 0===me?void 0:me.body),{},{text:i.value})}:{type:"image",message:"",body:Object(M["a"])(Object(M["a"])({},null===me||void 0===me?void 0:me.body),{},{url:o.value})},s=Object(M["a"])(Object(M["a"])(Object(M["a"])({},me),a),{},{user:Object(M["a"])({},c.value),created_at:new Date}),n=null===(t=v.value)||void 0===t?void 0:t.sendMessageSuccess;"[object Function]"===Object.prototype.toString.call(n)&&n(s)}function O(){return g.apply(this,arguments)}function g(){return g=Object(w["a"])(regeneratorRuntime.mark((function e(){var t,a,s,c,n,l;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:if(a="text"===d.value,(!a||i.value)&&(a||o.value)){e.next=4;break}return k["a"].error("消息内容不能为空或者您还未选中图片！"),e.abrupt("return");case 4:return j(a),u.value.loading=!0,e.next=8,Object(N["b"])((function(){return b()}));case 8:s=e.sent,c=Object(y["a"])(s,2),n=c[0],l=c[1],n?(u.value={loading:!1,error:n},k["a"].error("Error：".concat(Object(N["a"])(n)))):null!==l&&void 0!==l&&null!==(t=l.data)&&void 0!==t&&t.sendMessage&&(u.value={loading:!1,error:""},i.value="");case 13:case"end":return e.stop()}}),e)}))),g.apply(this,arguments)}return{content:i,chat:n,sendMessage:O,messageStatus:u,showMessageView:l,messagesViewRef:v}},computed:{chatName:function(){var e,t,a;return(null===(e=this.chat)||void 0===e?void 0:e.subject)||(null===(t=this.chat)||void 0===t||null===(a=t.withUser)||void 0===a?void 0:a.name)||"聊天消息"}}};a("9377");fe.render=p,fe.__scopeId="data-v-4b7e30d4";t["default"]=fe},"0656":function(e,t,a){},3739:function(e,t,a){},6384:function(e,t,a){},"74d6":function(e,t,a){"use strict";a("0656")},9377:function(e,t,a){"use strict";a("fc33")},"96c6":function(e,t,a){"use strict";a("cd6d")},c410:function(e,t,a){"use strict";a("6384")},c740:function(e,t,a){"use strict";var s=a("23e7"),c=a("b727").findIndex,n=a("44d2"),l="findIndex",i=!0;l in[]&&Array(1)[l]((function(){i=!1})),s({target:"Array",proto:!0,forced:i},{findIndex:function(e){return c(this,e,arguments.length>1?arguments[1]:void 0)}}),n(l)},cd6d:function(e,t,a){},cedf:function(e,t,a){"use strict";a("3739")},d76c:function(e,t,a){"use strict";var s=a("7a23");Object(s["Q"])("data-v-5999f142");var c={class:"ld-container"},n=Object(s["n"])("div",{class:"ld-ellipsis"},[Object(s["n"])("div"),Object(s["n"])("div"),Object(s["n"])("div"),Object(s["n"])("div")],-1),l=[n];function i(e,t,a,n,i,o){return Object(s["N"])(),Object(s["m"])("div",c,l)}Object(s["O"])();var o={setup:function(){return{}}};a("96c6");o.render=i,o.__scopeId="data-v-5999f142";t["a"]=o},fc33:function(e,t,a){}}]);
//# sourceMappingURL=chunk-b2584b4a.626a2ea0.js.map