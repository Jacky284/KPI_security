import{a as e,n as t,t as n}from"./jsx-runtime-B-hcVAMW.js";import{o as r}from"./app-BFa-8dMK.js";import{t as i}from"./button-a-oLk_Tx.js";var a=e(t(),1),o=n();function s({url:e,disabled:t}){let[n,s]=(0,a.useState)(!1);return(0,o.jsxs)(o.Fragment,{children:[(0,o.jsx)(i,{onClick:async i=>{if(i.preventDefault(),t||n)return;s(!0);let a=window.open(``,`_blank`);a&&a.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>Mempersiapkan PDF...</title>
          <style>
            body, html { margin: 0; padding: 0; height: 100vh; overflow: hidden; background-color: #f8fafc; font-family: -apple-system, sans-serif; }
            .loader-container { display: flex; justify-content: center; align-items: center; height: 100vh; width: 100vw; position: absolute; top: 0; left: 0; z-index: 10; background: #f8fafc; transition: opacity 0.3s; }
            .card { background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; text-align: center; max-width: 320px; width: 100%; margin: 1rem; }
            h3 { margin: 0 0 0.5rem 0; font-size: 1.125rem; color: #0f172a; }
            p { margin: 0; font-size: 0.875rem; color: #64748b; }
            .track { width: 100%; background-color: #f1f5f9; border-radius: 9999px; height: 0.5rem; margin-top: 1.5rem; overflow: hidden; position: relative; }
            .bar { background-color: #dc2626; height: 100%; border-radius: 9999px; position: absolute; top: 0; width: 40%; animation: slideRight 1s infinite alternate ease-in-out; }
            @keyframes slideRight { 0% { left: 0%; } 100% { left: 60%; } }
            #pdf-frame { width: 100vw; height: 100vh; border: none; position: absolute; top: 0; left: 0; z-index: 1; display: none; }
          </style>
        </head>
        <body>
          <div class="loader-container" id="loader">
            <div class="card">
              <h3>PDF Sedang Disusun</h3>
              <p>Mohon tunggu sebentar...</p>
              <div class="track"><div class="bar"></div></div>
            </div>
          </div>
          <iframe id="pdf-frame"></iframe>
        </body>
        </html>
      `);try{let t=await fetch(e,{headers:{"X-Bypass-IDM":`1`}});if(!t.ok)throw Error(`Gagal mengekspor PDF`);let n=await t.blob(),r=new Blob([n],{type:`application/pdf`}),i=window.URL.createObjectURL(r);a?(a.document.getElementById(`pdf-frame`).src=i,a.document.getElementById(`pdf-frame`).style.display=`block`,a.document.getElementById(`loader`).style.display=`none`,a.document.title=`Preview PDF`):window.location.href=i}catch(e){console.error(e),r.error(`Terjadi kesalahan saat menyusun PDF. Silakan coba lagi.`),a&&a.close()}finally{s(!1)}},variant:`outline`,size:`sm`,className:`w-full sm:w-auto bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 border-red-200 ${t?`pointer-events-none opacity-50`:``}`,disabled:t||n,children:n?`Menyiapkan PDF...`:`Export PDF`}),n&&(0,o.jsx)(`div`,{className:`fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-sm`,children:(0,o.jsx)(`div`,{className:`flex flex-col items-center gap-6 bg-card p-8 rounded-xl shadow-lg border max-w-sm w-full mx-4`,children:(0,o.jsxs)(`div`,{className:`flex flex-col items-center gap-2 w-full`,children:[(0,o.jsx)(`h3`,{className:`font-bold text-lg text-foreground text-center`,children:`PDF Sedang Disusun`}),(0,o.jsx)(`p`,{className:`text-sm text-muted-foreground text-center`,children:`Mohon tunggu sebentar...`}),(0,o.jsx)(`div`,{className:`w-full bg-muted rounded-full h-2 mt-4 overflow-hidden relative`,children:(0,o.jsx)(`div`,{className:`bg-red-600 h-2 rounded-full absolute top-0`,style:{width:`40%`,animation:`slideRight 1s infinite alternate ease-in-out`}})}),(0,o.jsx)(`style`,{children:`
                @keyframes slideRight {
                  0% { left: 0%; }
                  100% { left: 60%; }
                }
              `})]})})})]})}export{s as t};