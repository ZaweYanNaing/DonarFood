import { useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { AppLayout } from '@/components/app-shell';
import { Textarea } from '@/components/ui/textarea';

export default function IssueCreate() {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        description: '',
        location: '',
        address: '',
        latitude: '',
        longitude: '',
        required_items: '',
        expires_at: '',
        images: [] as File[],
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/issues');
    };

    return (
        <AppLayout>
            <Head title="Create Donation Issue" />

            <div className="max-w-2xl mx-auto">
                <h1 className="text-3xl font-bold mb-6">Create Donation Issue</h1>

                <Card>
                    <CardHeader>
                        <CardTitle>Tell us about the donation opportunity</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6" encType="multipart/form-data">
                            <div>
                                <Label htmlFor="title">Title *</Label>
                                <Input
                                    id="title"
                                    value={data.title}
                                    onChange={(e) => setData('title', e.target.value)}
                                    placeholder="e.g., Food Drive at Community Center"
                                    required
                                />
                                {errors.title && <p className="text-red-500 text-sm mt-1">{errors.title}</p>}
                            </div>

                            <div>
                                <Label htmlFor="description">Description *</Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Describe the donation opportunity, including what kind of food is needed..."
                                    rows={5}
                                    required
                                />
                                {errors.description && <p className="text-red-500 text-sm mt-1">{errors.description}</p>}
                            </div>

                            <div>
                                <Label htmlFor="location">Location Name</Label>
                                <Input
                                    id="location"
                                    value={data.location}
                                    onChange={(e) => setData('location', e.target.value)}
                                    placeholder="e.g., Downtown Community Center"
                                />
                                {errors.location && <p className="text-red-500 text-sm mt-1">{errors.location}</p>}
                            </div>

                            <div>
                                <Label htmlFor="address">Address</Label>
                                <Input
                                    id="address"
                                    value={data.address}
                                    onChange={(e) => setData('address', e.target.value)}
                                    placeholder="Full address for pickup/dropoff"
                                />
                                {errors.address && <p className="text-red-500 text-sm mt-1">{errors.address}</p>}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <Label htmlFor="latitude">Latitude</Label>
                                    <Input
                                        id="latitude"
                                        type="number"
                                        step="any"
                                        value={data.latitude}
                                        onChange={(e) => setData('latitude', e.target.value)}
                                        placeholder="e.g., 40.7128"
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="longitude">Longitude</Label>
                                    <Input
                                        id="longitude"
                                        type="number"
                                        step="any"
                                        value={data.longitude}
                                        onChange={(e) => setData('longitude', e.target.value)}
                                        placeholder="e.g., -74.0060"
                                    />
                                </div>
                            </div>

                            <div>
                                <Label htmlFor="required_items">Items Needed</Label>
                                <Input
                                    id="required_items"
                                    value={data.required_items}
                                    onChange={(e) => setData('required_items', e.target.value)}
                                    placeholder="e.g., Canned goods, Rice, Pasta, Fresh vegetables"
                                />
                                {errors.required_items && <p className="text-red-500 text-sm mt-1">{errors.required_items}</p>}
                            </div>

                            <div>
                                <Label htmlFor="expires_at">Expiration Date (optional)</Label>
                                <Input
                                    id="expires_at"
                                    type="datetime-local"
                                    value={data.expires_at}
                                    onChange={(e) => setData('expires_at', e.target.value)}
                                />
                                {errors.expires_at && <p className="text-red-500 text-sm mt-1">{errors.expires_at}</p>}
                            </div>

                            <div>
                                <Label htmlFor="images">Photos (optional)</Label>
                                <Input
                                    id="images"
                                    type="file"
                                    accept="image/*"
                                    multiple
                                    onChange={(e) => {
                                        if (e.target.files) {
                                            setData('images', Array.from(e.target.files));
                                        }
                                    }}
                                />
                                <p className="text-sm text-muted-foreground mt-1">
                                    Upload photos of the location or what kind of donations are needed
                                </p>
                            </div>

                            <div className="flex justify-end gap-4">
                                <Button type="button" variant="outline" onClick={() => window.history.back()}>
                                    Cancel
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Creating...' : 'Create Issue'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}