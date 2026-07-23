import React, { useState, useEffect } from "react";
import { Head, router, useForm } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { ChevronDown, ChevronUp, Undo, Redo } from "lucide-react";

interface User {
  id_user: number;
  nama_lengkap: string;
  regu: string | null;
  role: string;
}

interface Jadwal {
  id_jadwal: number;
  id_anggota: number;
  bulan: string;
  tahun: number;
  jadwal_harian: Record<string, "Pagi" | "Malam" | "Libur">;
}

interface Props {
  anggotas: User[];
  jadwals: Record<number, Jadwal>;
  bulan: string;
  tahun: string;
}

interface CellCoord {
  row: number; // index in anggotas array
  col: number; // index in daysArray (0-based)
}

export default function Manage({ anggotas, jadwals, bulan, tahun }: Props) {
  const [selectedBulan, setSelectedBulan] = useState(bulan);
  const [selectedTahun, setSelectedTahun] = useState(tahun);

  const daysInMonth = new Date(parseInt(selectedTahun), parseInt(selectedBulan), 0).getDate();
  const daysArray = Array.from({ length: daysInMonth }, (_, i) => i + 1);

  const { data, setData, post, processing } = useForm({
    bulan: selectedBulan,
    tahun: selectedTahun,
    jadwal: {} as Record<number, Record<string, "Pagi" | "Malam" | "Libur">>,
  });

  const [history, setHistory] = useState<Record<number, Record<string, "Pagi" | "Malam" | "Libur">>[]>([]);
  const [historyIndex, setHistoryIndex] = useState(-1);

  const pushToHistory = (newJadwal: Record<number, Record<string, "Pagi" | "Malam" | "Libur">>) => {
    const newHistory = history.slice(0, historyIndex + 1);
    newHistory.push(newJadwal);
    setHistory(newHistory);
    setHistoryIndex(newHistory.length - 1);
  };

  const [collapsedRegus, setCollapsedRegus] = useState<Record<string, boolean>>({});

  useEffect(() => {
    const initialJadwal: Record<number, Record<string, "Pagi" | "Malam" | "Libur">> = {};
    anggotas.forEach(anggota => {
      initialJadwal[anggota.id_user] = {};
      daysArray.forEach(day => {
        const existingJadwal = jadwals[anggota.id_user]?.jadwal_harian;
        initialJadwal[anggota.id_user][day.toString()] = existingJadwal ? existingJadwal[day.toString()] || "Libur" : "Libur";
      });
    });
    setData(data => ({
      ...data,
      bulan: selectedBulan,
      tahun: selectedTahun,
      jadwal: initialJadwal
    }));
    setHistory([initialJadwal]);
    setHistoryIndex(0);
  }, [anggotas, jadwals, selectedBulan, selectedTahun]);

  const handleBulanChange = (newBulan: string) => {
    setSelectedBulan(newBulan);
    router.get('/jadwal/manage', { bulan: newBulan, tahun: selectedTahun });
  };

  const handleTahunChange = (newTahun: string) => {
    setSelectedTahun(newTahun);
    router.get('/jadwal/manage', { bulan: selectedBulan, tahun: newTahun });
  };

  const toggleShift = (current: string) => {
    if (current === "Libur") return "Pagi";
    if (current === "Pagi") return "Malam";
    return "Libur";
  };

  // --- Excel-like Drag to Fill Logic ---
  const [selection, setSelection] = useState<{ start: CellCoord, end: CellCoord } | null>(null);
  const [isDragging, setIsDragging] = useState(false);
  
  const [isDraggingFill, setIsDraggingFill] = useState(false);
  const [dragFillTarget, setDragFillTarget] = useState<CellCoord | null>(null);

  useEffect(() => {
    const handleMouseUpGlobal = () => {
      if (isDragging) setIsDragging(false);
      if (isDraggingFill) {
        applyFillPattern();
        setIsDraggingFill(false);
        setDragFillTarget(null);
      }
    };
    window.addEventListener('mouseup', handleMouseUpGlobal);
    return () => window.removeEventListener('mouseup', handleMouseUpGlobal);
  }, [isDragging, isDraggingFill, dragFillTarget, selection]);

  const handleMouseDown = (row: number, col: number, e: React.MouseEvent) => {
    if (e.button !== 0) return; // Only left click
    setSelection({ start: { row, col }, end: { row, col } });
    setIsDragging(true);
  };

  const handleMouseEnter = (row: number, col: number) => {
    if (isDragging && selection) {
      setSelection({ ...selection, end: { row, col } });
    } else if (isDraggingFill && selection) {
      setDragFillTarget({ row, col });
    }
  };

  const handleFillHandleMouseDown = (e: React.MouseEvent) => {
    e.stopPropagation();
    setIsDraggingFill(true);
    setDragFillTarget(null);
  };

  const updateJadwalRecursive = (
    currentJadwal: Record<number, Record<string, "Pagi" | "Malam" | "Libur">>, 
    triggerAnggota: User, 
    day: string, 
    newVal: "Pagi" | "Malam" | "Libur"
  ) => {
    if (!currentJadwal[triggerAnggota.id_user]) currentJadwal[triggerAnggota.id_user] = {};
    currentJadwal[triggerAnggota.id_user][day] = newVal;

    if (triggerAnggota.role === 'Danru') {
      anggotas.forEach(a => {
        if (a.regu === triggerAnggota.regu && a.id_user !== triggerAnggota.id_user) {
          if (!currentJadwal[a.id_user]) currentJadwal[a.id_user] = {};
          currentJadwal[a.id_user][day] = newVal;
        }
      });
    }
  };

  const handleCellClick = (rowIndex: number, colIndex: number, anggota: User, day: string, currentShift: string) => {
    // Only toggle if it's a simple click (not a drag)
    if (selection && (selection.start.row !== selection.end.row || selection.start.col !== selection.end.col)) {
      return;
    }

    const newJadwal = JSON.parse(JSON.stringify(data.jadwal));
    const newVal = toggleShift(currentShift) as any;
    updateJadwalRecursive(newJadwal, anggota, day, newVal);
    setData("jadwal", newJadwal);
    pushToHistory(newJadwal);
  };

  const applyFillPattern = () => {
    if (!selection || !dragFillTarget) return;

    const minRow = Math.min(selection.start.row, selection.end.row);
    const maxRow = Math.max(selection.start.row, selection.end.row);
    const minCol = Math.min(selection.start.col, selection.end.col);
    const maxCol = Math.max(selection.start.col, selection.end.col);

    const patternHeight = maxRow - minRow + 1;
    const patternWidth = maxCol - minCol + 1;

    // Calculate target boundaries
    const targetMinRow = Math.min(minRow, dragFillTarget.row);
    const targetMaxRow = Math.max(maxRow, dragFillTarget.row);
    const targetMinCol = Math.min(minCol, dragFillTarget.col);
    const targetMaxCol = Math.max(maxCol, dragFillTarget.col);

    const newJadwal = JSON.parse(JSON.stringify(data.jadwal));

    for (let r = targetMinRow; r <= targetMaxRow; r++) {
      for (let c = targetMinCol; c <= targetMaxCol; c++) {
        // Skip if inside original selection
        if (r >= minRow && r <= maxRow && c >= minCol && c <= maxCol) {
          continue;
        }

        const srcRow = minRow + ((r - minRow) % patternHeight);
        let srcColOffset = (c - minCol) % patternWidth;
        if (srcColOffset < 0) srcColOffset += patternWidth; // handle dragging left
        let srcRowOffset = (r - minRow) % patternHeight;
        if (srcRowOffset < 0) srcRowOffset += patternHeight; // handle dragging up

        const srcRowFinal = minRow + srcRowOffset;
        const srcColFinal = minCol + srcColOffset;

        const srcUserId = anggotas[srcRowFinal].id_user;
        const srcDay = daysArray[srcColFinal].toString();
        
        const targetUser = anggotas[r];
        const targetDay = daysArray[c].toString();

        const val = newJadwal[srcUserId]?.[srcDay] || "Libur";
        
        updateJadwalRecursive(newJadwal, targetUser, targetDay, val as any);
      }
    }

    setData("jadwal", newJadwal);
    pushToHistory(newJadwal);
    
    // Expand selection to include filled area
    setSelection({
      start: { row: targetMinRow, col: targetMinCol },
      end: { row: targetMaxRow, col: targetMaxCol }
    });
  };

  const handleUndo = () => {
    if (historyIndex > 0) {
      const newIndex = historyIndex - 1;
      setHistoryIndex(newIndex);
      setData("jadwal", history[newIndex]);
    }
  };

  const handleRedo = () => {
    if (historyIndex < history.length - 1) {
      const newIndex = historyIndex + 1;
      setHistoryIndex(newIndex);
      setData("jadwal", history[newIndex]);
    }
  };

  const isCellSelected = (row: number, col: number) => {
    if (!selection) return false;
    const minRow = Math.min(selection.start.row, selection.end.row);
    const maxRow = Math.max(selection.start.row, selection.end.row);
    const minCol = Math.min(selection.start.col, selection.end.col);
    const maxCol = Math.max(selection.start.col, selection.end.col);
    return row >= minRow && row <= maxRow && col >= minCol && col <= maxCol;
  };

  const isCellFillTarget = (row: number, col: number) => {
    if (!selection || !dragFillTarget || !isDraggingFill) return false;
    const minRow = Math.min(selection.start.row, selection.end.row);
    const maxRow = Math.max(selection.start.row, selection.end.row);
    const minCol = Math.min(selection.start.col, selection.end.col);
    const maxCol = Math.max(selection.start.col, selection.end.col);
    
    const targetMinRow = Math.min(minRow, dragFillTarget.row);
    const targetMaxRow = Math.max(maxRow, dragFillTarget.row);
    const targetMinCol = Math.min(minCol, dragFillTarget.col);
    const targetMaxCol = Math.max(maxCol, dragFillTarget.col);

    // It's in the target area but NOT in the original selection area
    const inTargetArea = row >= targetMinRow && row <= targetMaxRow && col >= targetMinCol && col <= targetMaxCol;
    const inSelectionArea = row >= minRow && row <= maxRow && col >= minCol && col <= maxCol;
    return inTargetArea && !inSelectionArea;
  };

  const isBottomRightOfSelection = (row: number, col: number) => {
    if (!selection) return false;
    const maxRow = Math.max(selection.start.row, selection.end.row);
    const maxCol = Math.max(selection.start.col, selection.end.col);
    return row === maxRow && col === maxCol;
  };

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/jadwal/manage', {
      preserveScroll: true,
      onSuccess: () => alert('Jadwal berhasil disimpan!')
    });
  };

  let currentRegu: string | null = null;
  const tableRows: React.ReactNode[] = [];

  anggotas.forEach((anggota, rowIndex) => {
    const reguDisplay = anggota.regu || "Tanpa Regu";
    
    // Check if regu is collapsed
    const isCollapsed = collapsedRegus[reguDisplay];

    if (currentRegu !== reguDisplay) {
      currentRegu = reguDisplay;
      tableRows.push(
        <tr key={`regu-${currentRegu}`} className="bg-muted/50 border-y">
          <td className="py-1 px-2 font-bold text-left sticky left-0 bg-muted z-30 text-primary border-r shadow-sm text-xs">
            <div className="flex justify-between items-center">
              <span>{currentRegu}</span>
              <Button 
                type="button"
                variant="ghost" 
                size="icon" 
                className="h-5 w-5 ml-2"
                onClick={(e) => {
                  e.preventDefault();
                  e.stopPropagation();
                  setCollapsedRegus(prev => ({ ...prev, [reguDisplay]: !prev[reguDisplay] }));
                }}
              >
                {isCollapsed ? <ChevronDown className="h-4 w-4" /> : <ChevronUp className="h-4 w-4" />}
              </Button>
            </div>
          </td>
          <td colSpan={daysArray.length} className="bg-muted/50 shadow-sm border-r"></td>
        </tr>
      );
    }

    // Don't render anggota if collapsed (but always render Danru)
    if (isCollapsed && anggota.role === 'Anggota') {
      return;
    }

    // Nama lengkap original saja (tidak ada tulisan - Danru)
    const displayName = anggota.nama_lengkap;
    
    tableRows.push(
      <tr key={`user-${anggota.id_user}`} className={`border-b hover:bg-muted/10 ${rowIndex % 2 === 0 ? 'bg-background' : 'bg-muted/5'}`}>
        <td className="p-2 lg:px-1.5 lg:py-1 font-semibold border-r sticky left-0 bg-background text-left truncate max-w-[200px] lg:max-w-[140px] z-20 text-xs">
          <div className="flex items-center gap-1.5 truncate">
            {anggota.role === 'Danru' && <span className="w-1.5 h-1.5 rounded-full bg-primary shrink-0" title="Danru"></span>}
            <span className="truncate">{displayName}</span>
          </div>
        </td>
        {daysArray.map((day, colIndex) => {
          const shift = data.jadwal[anggota.id_user]?.[day.toString()] || "Libur";
          let colorClass = "bg-background text-muted-foreground";
          if (shift === "Pagi") colorClass = "bg-blue-100 text-blue-700 font-bold border-blue-200";
          if (shift === "Malam") colorClass = "bg-indigo-100 text-indigo-700 font-bold border-indigo-200";
          
          const selected = isCellSelected(rowIndex, colIndex);
          const fillTarget = isCellFillTarget(rowIndex, colIndex);
          const showFillHandle = isBottomRightOfSelection(rowIndex, colIndex);

          return (
            <td 
              key={day} 
              className={`border-r p-0 cursor-cell relative select-none
                ${selected ? 'ring-2 ring-inset ring-primary z-10' : ''}
                ${fillTarget ? 'bg-primary/10 ring-1 ring-inset ring-primary border-primary/30' : ''}
              `}
              onMouseDown={(e) => handleMouseDown(rowIndex, colIndex, e)}
              onMouseEnter={() => handleMouseEnter(rowIndex, colIndex)}
              onClick={() => handleCellClick(rowIndex, colIndex, anggota, day.toString(), shift)}
            >
              <div className={`w-full h-8 flex items-center justify-center ${selected ? '' : colorClass} ${selected ? (shift === 'Pagi' ? 'bg-blue-200 text-blue-800' : shift === 'Malam' ? 'bg-indigo-200 text-indigo-800' : 'bg-muted text-foreground') : ''}`}>
                {shift === "Libur" ? "-" : shift.charAt(0)}
              </div>
              {showFillHandle && (
                <div 
                  className="absolute -bottom-1.5 -right-1.5 w-3 h-3 bg-primary border border-white cursor-crosshair z-30"
                  onMouseDown={handleFillHandleMouseDown}
                />
              )}
            </td>
          );
        })}
      </tr>
    );
  });

  return (
    <>
      <Head title="Manajemen Jadwal Bulanan" />
      <div className="flex flex-col gap-6 max-w-7xl mx-auto">
        <div>
          <h1 className="text-2xl font-bold text-primary tracking-tight">Pengaturan Jadwal Bulanan</h1>
          <p className="text-sm text-muted-foreground">Atur jadwal shift anggota secara cepat menggunakan fitur Drag-and-Fill. Klik dan tahan sel, lalu tarik ujung kanannya untuk menyalin pola jadwal.</p>
        </div>

        <Card className="shadow-xs border-2">
          <CardHeader className="bg-muted/30 border-b flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4">
            <div>
              <CardTitle className="text-md font-bold">Grid Jadwal</CardTitle>
              <CardDescription>Klik/Drag sel untuk merubah jadwal. Jadwal Anggota akan otomatis mengikuti jadwal Danru di regunya.</CardDescription>
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <select 
                value={selectedBulan} 
                onChange={e => handleBulanChange(e.target.value)}
                className="p-1.5 border rounded text-sm bg-background cursor-pointer"
              >
                {Array.from({length: 12}, (_, i) => i + 1).map(m => (
                  <option key={m} value={m.toString().padStart(2, '0')}>
                    {new Date(0, m - 1).toLocaleString('id-ID', { month: 'long' })}
                  </option>
                ))}
              </select>
              <input 
                type="number" 
                value={selectedTahun}
                onChange={e => handleTahunChange(e.target.value)}
                className="p-1.5 border rounded text-sm w-20 bg-background"
              />
            </div>
          </CardHeader>
          <form onSubmit={submit} className="flex flex-col">
            <CardContent className="p-0 overflow-x-auto select-none w-full max-w-[100vw]">
              <div className="min-w-max lg:min-w-0 lg:w-full pb-4">
                <table className="w-full text-xs text-center border-collapse table-auto lg:table-fixed">
                  <thead>
                    <tr className="bg-muted text-foreground border-b border-muted-foreground/20">
                      <th className="p-2 lg:px-1.5 lg:py-1 border-r sticky left-0 bg-muted z-40 w-48 lg:w-36 text-left shadow-sm text-xs">Nama Anggota</th>
                      {daysArray.map(day => (
                        <th key={day} className="p-2 lg:p-0.5 border-r w-8 min-w-[32px] lg:min-w-0 lg:w-auto text-xs">{day}</th>
                      ))}
                    </tr>
                  </thead>
                  <tbody>
                    {anggotas.length === 0 ? (
                      <tr>
                        <td colSpan={daysArray.length + 1} className="p-4 text-center text-muted-foreground">
                          Tidak ada anggota ditemukan.
                        </td>
                      </tr>
                    ) : (
                      tableRows
                    )}
                  </tbody>
                </table>
              </div>
            </CardContent>

            <div className="p-4 bg-muted/20 border-t flex justify-between items-center gap-2 sticky bottom-0 z-50 left-0 right-0 w-full shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
              <div className="text-xs text-muted-foreground hidden sm:block">
                Tips: <b>Klik 1x</b> pada sel untuk mengubah Pagi/Malam/Libur secara cepat tanpa drag.
              </div>
              <div className="flex gap-2">
                <Button type="button" variant="outline" size="icon" onClick={handleUndo} disabled={historyIndex <= 0} title="Undo">
                  <Undo className="w-4 h-4" />
                </Button>
                <Button type="button" variant="outline" size="icon" onClick={handleRedo} disabled={historyIndex >= history.length - 1} title="Redo">
                  <Redo className="w-4 h-4" />
                </Button>
                <Button type="submit" disabled={processing || anggotas.length === 0}>
                  {processing ? "Menyimpan..." : "Simpan Jadwal"}
                </Button>
              </div>
            </div>
          </form>
        </Card>
      </div>
    </>
  );
}

Manage.layout = (page: React.ReactNode) => <DashboardLayout>{page}</DashboardLayout>;