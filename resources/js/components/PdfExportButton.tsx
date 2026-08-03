import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { toast } from 'sonner';

interface Props {
  url: string;
  disabled?: boolean;
}

export function PdfExportButton({ url, disabled }: Props) {
  const [isExporting, setIsExporting] = useState(false);

  const handleExport = async (e: React.MouseEvent) => {
    e.preventDefault();
    if (disabled || isExporting) return;
    
    setIsExporting(true);
    
    // Buka tab baru dengan loading state
    const newTab = window.open('', '_blank');
    if (newTab) {
      newTab.document.write(`
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
      `);
    }

    try {
      const response = await fetch(url, {
        headers: {
          'X-Bypass-IDM': '1'
        }
      });
      if (!response.ok) throw new Error('Gagal mengekspor PDF');
      
      const blob = await response.blob();
      const pdfBlob = new Blob([blob], { type: 'application/pdf' });
      const blobUrl = window.URL.createObjectURL(pdfBlob);
      
      if (newTab) {
        // Render PDF di dalam iframe dan sembunyikan loading screen
        newTab.document.getElementById('pdf-frame').src = blobUrl;
        newTab.document.getElementById('pdf-frame').style.display = 'block';
        newTab.document.getElementById('loader').style.display = 'none';
        newTab.document.title = "Preview PDF";
      } else {
        window.location.href = blobUrl;
      }
    } catch (error) {
      console.error(error);
      toast.error("Terjadi kesalahan saat menyusun PDF. Silakan coba lagi.");
      if (newTab) newTab.close();
    } finally {
      setIsExporting(false);
    }
  };

  return (
    <>
      <Button 
        onClick={handleExport}
        variant="outline" 
        size="sm" 
        className={`w-full sm:w-auto bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 border-red-200 ${disabled ? 'pointer-events-none opacity-50' : ''}`}
        disabled={disabled || isExporting}
      >
        {isExporting ? 'Menyiapkan PDF...' : 'Export PDF'}
      </Button>

      {isExporting && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-sm">
          <div className="flex flex-col items-center gap-6 bg-card p-8 rounded-xl shadow-lg border max-w-sm w-full mx-4">
            <div className="flex flex-col items-center gap-2 w-full">
              <h3 className="font-bold text-lg text-foreground text-center">PDF Sedang Disusun</h3>
              <p className="text-sm text-muted-foreground text-center">Mohon tunggu sebentar...</p>
              
              <div className="w-full bg-muted rounded-full h-2 mt-4 overflow-hidden relative">
                <div 
                  className="bg-red-600 h-2 rounded-full absolute top-0" 
                  style={{ width: '40%', animation: 'slideRight 1s infinite alternate ease-in-out' }}
                ></div>
              </div>
              <style>{`
                @keyframes slideRight {
                  0% { left: 0%; }
                  100% { left: 60%; }
                }
              `}</style>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
