import React, { useState, useEffect } from "react";
import { Head, router, useForm } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { ChevronDown, ChevronUp, Undo, Redo, RefreshCw, LayoutGrid, Smartphone, Sun, Moon, Coffee } from "lucide-react";

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

  const getDayName = (yearStr: string, monthStr: string, dayNum: number) => {
    const date = new Date(parseInt(yearStr), parseInt(monthStr) - 1, dayNum);
    const days = ['Mg', 'Sn', 'Sl', 'Rb', 'Km', 'Jm', 'Sb'];
    return days[date.getDay()];
  };

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

  const handleSyncDanru = () => {
    const newJadwal = { ...data.jadwal };
    const regus = [...new Set(anggotas.map(a => a.regu).filter(Boolean))];
    
    let hasChanges = false;
    regus.forEach(regu => {
      const danru = anggotas.find(a => a.regu === regu && a.role === 'Danru');
      if (danru) {
        const danruSchedule = newJadwal[danru.id_user];
        if (danruSchedule) {
          anggotas.forEach(anggota => {
            if (anggota.regu === regu && anggota.role === 'Anggota') {
              newJadwal[anggota.id_user] = { ...danruSchedule };
              hasChanges = true;
            }
          });
        }
      }
    });

    if (hasChanges) {
      setData("jadwal", newJadwal);
      pushToHistory(newJadwal);
    }
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

  const setAllShiftsForMember = (id_user: number, shiftType: "Pagi" | "Malam" | "Libur") => {
    const newJadwal = JSON.parse(JSON.stringify(data.jadwal));
    const targetUser = anggotas.find(a => a.id_user === id_user);
    daysArray.forEach(d => {
      if (targetUser) {
        updateJadwalRecursive(newJadwal, targetUser, d.toString(), shiftType);
      } else {
        if (!newJadwal[id_user]) newJadwal[id_user] = {};
        newJadwal[id_user][d.toString()] = shiftType;
      }
    });
    setData("jadwal", newJadwal);
    pushToHistory(newJadwal);
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
      onSuccess: () => {}
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
          <CardHeader className="bg-muted/30 border-b flex flex-col md:flex-row md:items-end justify-between gap-3 px-4 pt-2 pb-3">
            <div className="flex items-center gap-4">
              <div className="flex flex-col gap-1">
                <label className="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">BULAN</label>
                <select 
                  value={selectedBulan} 
                  onChange={e => handleBulanChange(e.target.value)}
                  className="p-2 border rounded-md text-sm bg-background cursor-pointer"
                >
                  {Array.from({length: 12}, (_, i) => i + 1).map(m => (
                    <option key={m} value={m.toString().padStart(2, '0')}>
                      {new Date(0, m - 1).toLocaleString('id-ID', { month: 'long' })}
                    </option>
                  ))}
                </select>
              </div>

              <div className="flex flex-col gap-1">
                <label className="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">TAHUN</label>
                <input 
                  type="number" 
                  value={selectedTahun}
                  onChange={e => handleTahunChange(e.target.value)}
                  className="p-2 border rounded-md text-sm w-24 bg-background"
                />
              </div>
            </div>

            <div className="flex items-center gap-2 md:ml-auto self-end">
              <Button asChild variant="outline" size="sm" className="bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 border-green-200">
                <a href={`/export/jadwal-bulanan?type=excel&bulan=${selectedBulan}&tahun=${selectedTahun}`} target="_blank">
                  Export Excel
                </a>
              </Button>
              <Button asChild variant="outline" size="sm" className="bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 border-red-200">
                <a href={`/export/jadwal-bulanan?type=pdf&bulan=${selectedBulan}&tahun=${selectedTahun}`} target="_blank">
                  Export PDF
                </a>
              </Button>
            </div>
          </CardHeader>
          <form onSubmit={submit} className="flex flex-col">
            {/* Mobile View: Auto-shown on mobile/tablet (< lg) */}
            <div className="p-3 sm:p-4 flex flex-col gap-4 bg-muted/10 block lg:hidden">
              <div className="flex flex-wrap items-center justify-between p-2.5 bg-card border rounded-lg text-xs gap-2 shadow-xs">
                <span className="font-semibold text-muted-foreground">Legenda Shift:</span>
                <div className="flex gap-1.5 flex-wrap">
                  <span className="px-2 py-0.5 bg-blue-100 text-blue-700 font-bold rounded border border-blue-200">P = Pagi</span>
                  <span className="px-2 py-0.5 bg-indigo-100 text-indigo-700 font-bold rounded border border-indigo-200">M = Malam</span>
                  <span className="px-2 py-0.5 bg-muted text-muted-foreground font-bold rounded border"> - = Libur</span>
                </div>
              </div>

              {anggotas.length === 0 ? (
                <div className="p-6 text-center text-muted-foreground bg-card border rounded-lg">
                  Tidak ada anggota ditemukan.
                </div>
              ) : (
                (() => {
                  const grouped: Record<string, User[]> = {};
                  anggotas.forEach((a) => {
                    const r = a.regu || "Tanpa Regu";
                    if (!grouped[r]) grouped[r] = [];
                    grouped[r].push(a);
                  });

                  return Object.entries(grouped).map(([reguName, members]) => {
                    const isCollapsed = collapsedRegus[reguName];

                    return (
                      <div key={`mobile-regu-group-${reguName}`} className="flex flex-col gap-3">
                        {/* Regu Accordion Header */}
                        <div 
                          className="flex items-center justify-between p-3 bg-muted/60 border rounded-lg shadow-xs cursor-pointer select-none hover:bg-muted/80 transition-colors"
                          onClick={() => setCollapsedRegus(prev => ({ ...prev, [reguName]: !prev[reguName] }))}
                        >
                          <div className="flex items-center gap-2">
                            <span className="font-bold text-primary text-sm">{reguName}</span>
                            <span className="text-[11px] bg-background text-muted-foreground font-semibold px-2 py-0.5 rounded-full border">
                              {members.length} Anggota
                            </span>
                          </div>
                          <Button 
                            type="button" 
                            variant="ghost" 
                            size="sm" 
                            className="h-7 w-7 p-0"
                            onClick={(e) => {
                              e.stopPropagation();
                              setCollapsedRegus(prev => ({ ...prev, [reguName]: !prev[reguName] }));
                            }}
                          >
                            {isCollapsed ? <ChevronDown className="h-4 w-4" /> : <ChevronUp className="h-4 w-4" />}
                          </Button>
                        </div>

                        {/* Member Cards (hidden when collapsed) */}
                        {!isCollapsed && members.map((anggota) => (
                          <Card key={`mobile-user-${anggota.id_user}`} className="border shadow-xs">
                            <div className="p-3 bg-muted/30 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                              <div className="flex items-center gap-2">
                                {anggota.role === 'Danru' && <span className="w-2.5 h-2.5 rounded-full bg-primary shrink-0" title="Danru" />}
                                <span className="font-bold text-sm">{anggota.nama_lengkap}</span>
                                <span className="text-xs text-muted-foreground bg-background px-2 py-0.5 rounded border">{reguName}</span>
                                {anggota.role === 'Danru' && <span className="text-[10px] bg-red-100 text-red-700 font-bold px-1.5 py-0.5 rounded">DANRU</span>}
                              </div>

                              <div className="flex items-center gap-1">
                                <span className="text-[10px] font-bold text-muted-foreground uppercase mr-1">Set All:</span>
                                <Button type="button" variant="outline" size="sm" className="h-7 px-2 text-[11px] bg-blue-50 text-blue-700 hover:bg-blue-100 border-blue-200" onClick={() => setAllShiftsForMember(anggota.id_user, 'Pagi')}>
                                  Pagi
                                </Button>
                                <Button type="button" variant="outline" size="sm" className="h-7 px-2 text-[11px] bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border-indigo-200" onClick={() => setAllShiftsForMember(anggota.id_user, 'Malam')}>
                                  Malam
                                </Button>
                                <Button type="button" variant="outline" size="sm" className="h-7 px-2 text-[11px] bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-200" onClick={() => setAllShiftsForMember(anggota.id_user, 'Libur')}>
                                  Libur
                                </Button>
                              </div>
                            </div>

                            <CardContent className="p-3">
                              <div className="grid grid-cols-7 gap-1.5 text-center">
                                {daysArray.map((day) => {
                                  const shift = data.jadwal[anggota.id_user]?.[day.toString()] || "Libur";
                                  const dayName = getDayName(selectedTahun, selectedBulan, day);
                                  const isSunday = dayName === 'Mg';
                                  
                                  let btnStyle = "bg-background text-muted-foreground border-muted-foreground/30";
                                  if (shift === "Pagi") btnStyle = "bg-blue-600 text-white font-bold border-blue-700 shadow-xs";
                                  if (shift === "Malam") btnStyle = "bg-indigo-600 text-white font-bold border-indigo-700 shadow-xs";

                                  return (
                                    <button
                                      key={day}
                                      type="button"
                                      onClick={() => {
                                        const newVal = toggleShift(shift) as any;
                                        const newJadwal = JSON.parse(JSON.stringify(data.jadwal));
                                        updateJadwalRecursive(newJadwal, anggota, day.toString(), newVal);
                                        setData("jadwal", newJadwal);
                                        pushToHistory(newJadwal);
                                      }}
                                      className={`flex flex-col items-center justify-center p-1 rounded-md border text-xs transition-all active:scale-95 min-h-[46px] cursor-pointer ${btnStyle}`}
                                    >
                                      <span className={`text-[9px] ${isSunday ? 'text-red-400 font-bold' : 'opacity-80'}`}>{dayName}</span>
                                      <span className="font-bold text-[11px]">{day}</span>
                                      <span className="text-[10px] mt-0.5">{shift === "Libur" ? "-" : shift === "Pagi" ? "P" : "M"}</span>
                                    </button>
                                  );
                                })}
                              </div>
                            </CardContent>
                          </Card>
                        ))}
                      </div>
                    );
                  });
                })()
              )}
            </div>

            {/* Desktop View: Auto-shown on large screens (>= lg) */}
            <CardContent className="p-0 overflow-x-auto select-none w-full max-w-[100vw] hidden lg:block">
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
                <Button type="button" variant="outline" size="icon" onClick={handleSyncDanru} title="Samakan jadwal Anggota dengan Danru">
                  <RefreshCw className="w-4 h-4" />
                </Button>
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